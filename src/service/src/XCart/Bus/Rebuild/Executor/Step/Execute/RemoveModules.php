<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Executor\Step\Execute;

use AppendIterator;
use FilesystemIterator;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use XCart\Bus\Core\Annotations\RebuildStep;
use XCart\Bus\Domain\Backup\BackupInterface;
use XCart\Bus\Domain\ModuleInfoProvider;
use XCart\Bus\Rebuild\Executor\ScriptState;
use XCart\Bus\Rebuild\Executor\Step\StepInterface;
use XCart\Bus\Rebuild\Executor\StepState;
use XCart\Bus\Rebuild\Scenario\ChangeUnitProcessor;
use XCart\Bus\System\FilesystemInterface;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service(arguments={"logger"="XCart\Bus\Core\Logger\Rebuild"})
 * @RebuildStep(script = "redeploy", weight = "13000")
 */
class RemoveModules implements StepInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @var BackupInterface
     */
    private $backup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $rebuildId;

    /**
     * @param FilesystemInterface $filesystem
     * @param ModuleInfoProvider  $moduleInfoProvider
     * @param BackupInterface     $backup
     * @param LoggerInterface     $logger
     */
    public function __construct(
        FilesystemInterface $filesystem,
        ModuleInfoProvider $moduleInfoProvider,
        BackupInterface $backup,
        LoggerInterface $logger
    ) {
        $this->filesystem         = $filesystem;
        $this->moduleInfoProvider = $moduleInfoProvider;
        $this->backup             = $backup;
        $this->logger             = $logger;
    }

    /**
     * @param ScriptState $scriptState
     *
     * @return int
     */
    public function getProgressMax(ScriptState $scriptState): int
    {
        return count($this->getTransitions($scriptState));
    }

    /**
     * @param ScriptState $scriptState
     * @param StepState   $stepState
     *
     * @return StepState
     */
    public function initialize(ScriptState $scriptState, StepState $stepState = null): StepState
    {
        $transitions = $this->getTransitions($scriptState);

        $this->logger->debug(
            __METHOD__,
            [
                'id'          => $scriptState->id,
                'transitions' => $transitions,
            ]
        );

        $state = new StepState([
            'id'                  => static::class,
            'state'               => StepState::STATE_INITIALIZED,
            'rebuildId'           => $scriptState->id,
            'remainTransitions'   => $transitions,
            'finishedTransitions' => [],
            'progressMax'         => $this->getProgressMax($scriptState),
            'progressValue'       => 0,
        ]);

        $state->currentActionInfo = $this->getCurrentActionInfoMessage($state);

        return $state;
    }

    /**
     * @param StepState $state
     * @param string    $action
     * @param array     $params
     *
     * @return StepState
     */
    public function execute(StepState $state, $action = self::ACTION_EXECUTE, array $params = []): StepState
    {
        $this->rebuildId = $state->rebuildId;

        $this->backup = $this->backup->load($state->rebuildId);

        $state = $this->processTransition($state);

        $state->state = !empty($state->remainTransitions)
            ? StepState::STATE_IN_PROGRESS
            : StepState::STATE_FINISHED_SUCCESSFULLY;

        $state->currentActionInfo  = $this->getCurrentActionInfoMessage($state);
        $state->finishedActionInfo = $this->getFinishedActionInfoMessage($state);

        return $state;
    }

    /**
     * @param ScriptState $scriptState
     *
     * @return array
     */
    private function getTransitions(ScriptState $scriptState): array
    {
        return $this->filterScriptTransitions($scriptState->transitions);
    }

    /**
     * @param StepState $state
     *
     * @return string[]
     */
    private function getCurrentActionInfoMessage(StepState $state): array
    {
        $finished = count($state->finishedTransitions);
        $total    = $finished + count($state->remainTransitions);

        return $total !== $finished
            ? [[
                   'message' => 'rebuild.remove_modules.state',
                   'params'  => [$finished, $total],
               ]]
            : [];
    }

    /**
     * @param StepState $state
     *
     * @return string[]
     */
    private function getFinishedActionInfoMessage(StepState $state): array
    {
        $finished = count($state->finishedTransitions);
        $total    = $finished + count($state->remainTransitions);

        return $total === $finished
            ? [[
                   'message' => 'rebuild.remove_modules.state.finished',
                   'params'  => [$finished, $total],
               ]]
            : [];
    }

    /**
     * @param StepState $state
     *
     * @return StepState
     */
    private function processTransition(StepState $state): StepState
    {
        $remainTransitions = $state->remainTransitions;
        if (empty($remainTransitions)) {
            return $state;
        }

        $finishedTransitions = $state->finishedTransitions;
        $transition          = current($state->remainTransitions);
        $id                  = $transition['id'];

        $progressValue = $state->progressValue;

        // main action
        $transition = $this->removeByTransition($transition);

        // update state
        $finishedTransitions[$id] = $transition;
        unset($remainTransitions[$id]);
        $progressValue++;

        // save state
        $state->remainTransitions   = $remainTransitions;
        $state->finishedTransitions = $finishedTransitions;
        $state->progressValue       = $progressValue;

        return $state;
    }

    /**
     * @param $transition
     *
     * @return array
     */
    private function removeByTransition($transition): array
    {
        $moduleInfo = $this->moduleInfoProvider->getModuleInfo($transition['id']);
        if ($moduleInfo && isset($moduleInfo['directories'])) {
            $this->backup->addReplaceRecord($this->getIterator($moduleInfo['directories']));
            $this->filesystem->remove($moduleInfo['directories']);

            $this->logger->debug(
                sprintf('Remove dirs'),
                [
                    'id'       => $this->rebuildId,
                    'modified' => $moduleInfo['directories'],
                ]
            );
        }

        return $transition;
    }

    /**
     * @param array[] $transitions
     *
     * @return array[]
     */
    private function filterScriptTransitions($transitions): array
    {
        return array_filter($transitions, static function ($transition) {
            return ChangeUnitProcessor::TRANSITION_REMOVE === $transition['transition'];
        });
    }

    /**
     * @param array $directories
     *
     * @return AppendIterator
     */
    private function getIterator(array $directories): AppendIterator
    {
        return array_reduce($directories, static function ($result, $directory) {
            /** @var AppendIterator $result */
            $result->append(new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
            ));

            return $result;
        }, new AppendIterator());
    }
}
