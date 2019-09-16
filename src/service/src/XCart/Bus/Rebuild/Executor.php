<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild;

use XCart\Bus\Exception\ScriptExecutionError;
use XCart\Bus\Rebuild\Executor\Script\ScriptInterface;
use XCart\Bus\Rebuild\Executor\ScriptFactory;
use XCart\Bus\Rebuild\Executor\ScriptState;
use XCart\Bus\Rebuild\Executor\Step\StepInterface;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service(arguments={"token"="x_cart.bus.user_token"})
 */
class Executor
{
    const STATE_EXECUTION_TTL = 60;

    /**
     * @var ScriptFactory
     */
    protected $scriptFactory;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param ScriptFactory $scriptFactory
     * @param string        $token
     */
    public function __construct(
        ScriptFactory $scriptFactory,
        $token
    ) {
        $this->scriptFactory = $scriptFactory;
        $this->token         = $token;
    }

    /**
     * Initializes the scenario execution, generates step script (user for common executions)
     *
     * @param string $type
     * @param array  $scenario
     *
     * @return ScriptState
     */
    public function initializeByScenario($type, array $scenario)
    {
        $script = $this->scriptFactory->createScript($type);
        if ($script === null) {
            return null;
        }

        $state = $script->initializeByTransitions($scenario['id'], $scenario['modulesTransitions']);

        if (!empty($scenario['store_metadata'])) {
            $state->storeMetadata = $scenario['store_metadata'];
        }

        $state->touch($this->token);

        if (!empty($scenario['returnUrl'])) {
            $state->returnUrl = $scenario['returnUrl'];
        }

        return $state;
    }

    /**
     * Initializes the scenario execution, generates step script (used for rollback)
     *
     * @param string      $type
     * @param ScriptState $state
     *
     * @return ScriptState
     */
    public function initializeByState($type, ScriptState $state)
    {
        $script = $this->scriptFactory->createScript($type);
        $state  = $script->initializeByState($state->id, $state);

        $state->touch($this->token);

        if (!empty($scenario['returnUrl'])) {
            $state->returnUrl = $scenario['returnUrl'];
        }

        return $state;
    }

    /**
     * Handles the result of the script execution with given state
     *
     * @param ScriptState $state
     * @param string      $action
     * @param array       $params
     *
     * @return ScriptState
     * @throws ScriptExecutionError
     */
    public function execute(ScriptState $state, $action, array $params = [])
    {
        $script = $this->scriptFactory->createScript($state['type']);

        if (!$script) {
            throw ScriptExecutionError::fromUnknownScript($state['type']);
        }

        if (!$this->canBeExecutedByCurrentUser($script, $state)) {
            throw ScriptExecutionError::fromNotOwnedProcess();
        }

        // @todo: proof of concept
        if (in_array($action, [
            StepInterface::ACTION_IGNORE,
            StepInterface::ACTION_RETRY,
            StepInterface::ACTION_RELEASE,
        ], true)) {
            $state->errors       = [];
            $state->errorMessage = '';
            $state->state        = ScriptState::STATE_IN_PROGRESS;
        }

        if ($script->canAcceptState($state)) {
            $state = $script->execute(clone $state, $action, $params);
            $state->touch($this->token);

        } else {
            throw ScriptExecutionError::fromUnacceptableStateExecution();
        }

        return $state;
    }

    /**
     * unused
     *
     * @param ScriptState $state
     *
     * @return ScriptState
     * @throws ScriptExecutionError
     */
    public function cancel(ScriptState $state)
    {
        $script = $this->scriptFactory->createScript($state['type']);

        if (!$this->canBeExecutedByCurrentUser($script, $state)) {
            throw ScriptExecutionError::fromNotOwnedProcess();
        }

        $state = $script->cancel($state);
        $state->touch($this->token);

        return $state;
    }

    /**
     * @param ScriptInterface $script
     * @param ScriptState     $state
     *
     * @return bool
     */
    protected function canBeExecutedByCurrentUser(ScriptInterface $script, ScriptState $state)
    {
        return !$script->isOwnerLocked()
            || $this->token === $state->token
            || ($state->lastModifiedTime + static::STATE_EXECUTION_TTL) < time();
    }
}
