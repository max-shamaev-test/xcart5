<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Executor\Step;

use Psr\Log\LoggerInterface;
use XCart\Bus\Exception\RebuildException;
use XCart\Bus\Rebuild\Executor\ScriptState;
use XCart\Bus\Rebuild\Executor\Step\Execute\CheckPacks;
use XCart\Bus\Rebuild\Executor\StepState;
use XCart\Bus\Rebuild\Scenario\ChangeUnitProcessor;

abstract class ANote implements StepInterface
{
    /**
     * @var string
     */
    private $rebuildId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param ScriptState $scriptState
     *
     * @return int
     */
    public function getProgressMax(ScriptState $scriptState): int
    {
        return (int) (bool) ($scriptState->isStepCompleted(CheckPacks::class)
            ? $this->getTransitions($scriptState)
            : $this->filterScriptTransitions($scriptState->transitions));
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
            'data'                => [],
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
     * @throws RebuildException
     */
    public function execute(StepState $state, $action = self::ACTION_EXECUTE, array $params = []): StepState
    {
        $this->rebuildId = $state->rebuildId;

        if ($action === self::ACTION_EXECUTE) {
            $state = $this->processTransitions($state);

            $this->reportNotes($state);
        } elseif ($action === self::ACTION_SKIP_STEP) {
            $state = $this->skipStep($state);
        }

        $state->state = !empty($state->remainTransitions)
            ? StepState::STATE_IN_PROGRESS
            : StepState::STATE_FINISHED_SUCCESSFULLY;

        $state->currentActionInfo  = $this->getCurrentActionInfoMessage($state);
        $state->finishedActionInfo = $this->getFinishedActionInfoMessage($state);

        return $state;
    }

    /**
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * @param array $transition
     *
     * @return array
     */
    abstract protected function getNotesListByTransition($transition): array;

    /**
     * @param array $transition
     */
    abstract protected function reportNotes($state): void;

    /**
     * @param ScriptState $scriptState
     *
     * @return array
     */
    private function getTransitions(ScriptState $scriptState): array
    {
        $transitions = [];

        $parentStepState = $scriptState->getCompletedStepState(CheckPacks::class);
        if ($parentStepState) {
            $transitions = $parentStepState->finishedTransitions ?: [];
        }

        if (empty($transitions)) {
            return [];
        }

        return array_filter(array_map(
            function ($transition) {
                if ($transition['transition'] !== ChangeUnitProcessor::TRANSITION_UPGRADE) {
                    return [];
                }

                $notes = $this->getNotesListByTransition($transition);
                if ($notes) {
                    return [
                        'id'           => $transition['id'],
                        'remain_notes' => array_values($notes),
                        'pack_dir'     => $transition['pack_dir'],
                    ];
                }

                return [];
            },
            $transitions
        ));
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

        return $total === $finished
            ? [[
                   'message' => 'rebuild.note.' . $this->getType() . '.state.finished',
                   'params'  => [$finished, $total, $this->getType()],
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
                   'message' => 'rebuild.note.' . $this->getType() . '.state.finished',
                   'params'  => [$finished, $total, $this->getType()],
               ]]
            : [];
    }

    /**
     * @param StepState $state
     *
     * @return StepState
     */
    private function processTransitions(StepState $state): StepState
    {
        $remainTransitions = $state->remainTransitions;
        if (empty($remainTransitions)) {
            unset($state->data);

            return $state;
        }

        $finishedTransitions = $state->finishedTransitions;
        $data                = $state->data;

        foreach ($remainTransitions as $transition) {
            $id = $transition['id'];

            foreach ($transition['remain_notes'] as $noteId => $note) {
                $this->logger->debug(
                    sprintf('Get note for transition %s (%s)', $id, $note),
                    [
                        'id' => $this->rebuildId,
                    ]
                );

                $data[$id][] = file_get_contents($transition['pack_dir'] . $note);
                unset($transition['remain_notes']);
            }

            $finishedTransitions[$id] = $transition;
            unset($remainTransitions[$id]);
        }

        $state->remainTransitions   = $remainTransitions;
        $state->finishedTransitions = $finishedTransitions;
        $state->data                = $data;
        ++$state->progressValue;

        return $state;
    }

    /**
     * @param StepState $state
     *
     * @return StepState
     */
    private function skipStep(StepState $state): StepState
    {
        $state->finishedTransitions = $state->remainTransitions;
        $state->remainTransitions   = [];
        $state->progressValue       = $state->progressMax;

        return $state;
    }

    /**
     * @param array[] $transitions
     *
     * @return array[]
     */
    private function filterScriptTransitions($transitions): array
    {
        return array_filter($transitions, static function ($transition) {
            return ChangeUnitProcessor::TRANSITION_UPGRADE === $transition['transition'];
        });
    }
}
