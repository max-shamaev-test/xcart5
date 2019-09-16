<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Executor\Step\Execute;

use Exception;
use Psr\Log\LoggerInterface;
use XCart\Bus\Core\Annotations\RebuildStep;
use XCart\Bus\Domain\Module;
use XCart\Bus\Domain\ModuleInfoProvider;
use XCart\Bus\Exception\RebuildException;
use XCart\Bus\Query\Data\CoreConfigDataSource;
use XCart\Bus\Query\Data\InstalledModulesDataSource;
use XCart\Bus\Query\Data\MarketplaceModulesDataSource;
use XCart\Bus\Query\Data\UploadedModulesDataSource;
use XCart\Bus\Rebuild\Executor\ScriptState;
use XCart\Bus\Rebuild\Executor\Step\StepInterface;
use XCart\Bus\Rebuild\Executor\StepState;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service(arguments={"logger"="XCart\Bus\Core\Logger\Rebuild"})
 * @RebuildStep(script = "redeploy", weight = "14000")
 * @RebuildStep(script = "self-upgrade", weight = "5000")
 * @RebuildStep(script = "install", weight = "2000")
 */
class UpdateDataSource implements StepInterface
{
    /**
     * @var InstalledModulesDataSource
     */
    private $installedModulesDataSource;

    /**
     * @var MarketplaceModulesDataSource
     */
    private $marketplaceModulesDataSource;

    /**
     * @var UploadedModulesDataSource
     */
    private $uploadedModulesDataSource;

    /**
     * @var CoreConfigDataSource
     */
    private $coreConfigDataSource;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $rebuildId;

    /**
     * @param InstalledModulesDataSource   $installedModulesDataSource
     * @param MarketplaceModulesDataSource $marketplaceModulesDataSource
     * @param UploadedModulesDataSource    $uploadedModulesDataSource
     * @param CoreConfigDataSource         $coreConfigDataSource
     * @param ModuleInfoProvider           $moduleInfoProvider
     * @param LoggerInterface              $logger
     */
    public function __construct(
        InstalledModulesDataSource $installedModulesDataSource,
        MarketplaceModulesDataSource $marketplaceModulesDataSource,
        UploadedModulesDataSource $uploadedModulesDataSource,
        CoreConfigDataSource $coreConfigDataSource,
        ModuleInfoProvider $moduleInfoProvider,
        LoggerInterface $logger
    ) {
        $this->installedModulesDataSource   = $installedModulesDataSource;
        $this->marketplaceModulesDataSource = $marketplaceModulesDataSource;
        $this->uploadedModulesDataSource    = $uploadedModulesDataSource;
        $this->coreConfigDataSource         = $coreConfigDataSource;
        $this->moduleInfoProvider           = $moduleInfoProvider;
        $this->logger                       = $logger;
    }

    /**
     * @param ScriptState $scriptState
     *
     * @return int
     */
    public function getProgressMax(ScriptState $scriptState): int
    {
        return 1;
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
     *
     * @throws RebuildException
     * @throws Exception
     */
    public function execute(StepState $state, $action = self::ACTION_EXECUTE, array $params = []): StepState
    {
        $this->rebuildId = $state->rebuildId;

        $this->processTransitions($state);
        $this->refreshInstalledModulesDataSource();

        $state->finishedTransitions = $state->remainTransitions;
        $state->remainTransitions   = [];
        $state->progressValue++;

        $state->state = StepState::STATE_FINISHED_SUCCESSFULLY;

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
        return $scriptState->transitions ?: [];
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

        return $state->state !== StepState::STATE_FINISHED_SUCCESSFULLY
            ? [[
                   'message' => 'rebuild.update_data_source.state',
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

        return $state->state === StepState::STATE_FINISHED_SUCCESSFULLY
            ? [[
                   'message' => 'rebuild.update_data_source.state.finished',
                   'params'  => [$finished, $total],
               ]]
            : [];
    }

    /**
     * @param StepState $state
     *
     * @throws RebuildException
     * @throws Exception
     */
    private function processTransitions(StepState $state): void
    {
        if ($state->remainTransitions) {
            foreach ($state->remainTransitions as $key => $transition) {
                /** @var Module $module */
                $module     = $this->installedModulesDataSource->find($transition['id']);
                $stateAfter = $transition['stateAfterTransition'];

                if (!empty($stateAfter['installedDateUpdate'])) {
                    $stateAfter['installedDate'] = time();
                    unset($stateAfter['installedDateUpdate']);
                }

                if ($module && $transition['id'] !== 'core') {
                    $state->remainTransitions[$key]['previous_info'] = $module->toArray();
                    if (isset($stateAfter['installed']) && $stateAfter['installed'] === false) {
                        $this->installedModulesDataSource->removeOne($module->id);

                    } else {
                        $module = $this->updateModuleRecord($module, $stateAfter);
                        $this->installedModulesDataSource->saveOne($module);
                    }

                } elseif (!$module) {
                    if ($stateAfter['installed'] === true) {
                        $module = $this->marketplaceModulesDataSource->findByVersion($transition['id'], $stateAfter['version']);

                        if ($module) {
                            $module = $this->prepareInstalledModule($this->updateModuleRecord($module, $stateAfter));
                            $this->installedModulesDataSource->installModule($module);
                        } else {
                            throw new RebuildException('Module ' . $transition['id'] . ' was not found in marketplace data source');
                        }
                    } else {
                        throw new RebuildException('Module ' . $transition['id'] . ' was not found in data source');
                    }
                }

                if ($this->uploadedModulesDataSource->find($transition['id'])) {
                    $this->uploadedModulesDataSource->removeOne($transition['id']);
                }

                $this->logger->debug(
                    sprintf('Data updated: %s', $transition['id']),
                    [
                        'id' => $this->rebuildId,
                    ]
                );
            }

            $this->coreConfigDataSource->saveOne(time(), 'dataDate');
        }
    }

    private function refreshInstalledModulesDataSource(): void
    {
        $existent = $this->installedModulesDataSource->updateModulesData();
        $missing  = $this->installedModulesDataSource->removeMissedModules();

        $this->logger->debug(
            __METHOD__,
            [
                'id'       => $this->rebuildId,
                'existent' => $existent,
                'missing'  => $missing,
            ]
        );
    }

    /**
     * @param Module $module
     * @param array  $stateAfter
     *
     * @return Module
     */
    private function updateModuleRecord(Module $module, $stateAfter): Module
    {
        $module->merge($stateAfter);

        return $module;
    }

    /**
     * @param Module $module
     *
     * @return Module
     */
    private function prepareInstalledModule(Module $module): Module
    {
        $module->merge($this->moduleInfoProvider->getModuleInfo($module->id));

        return $module;
    }
}
