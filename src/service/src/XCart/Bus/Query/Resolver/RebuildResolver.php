<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Query\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use XCart\Bus\Core\Annotations\Resolver;
use XCart\Bus\Domain\Module;
use XCart\Bus\Exception\ScriptExecutionError;
use XCart\Bus\Query\Context;
use XCart\Bus\Query\Data\InstalledModulesDataSource;
use XCart\Bus\Query\Data\ScenarioDataSource;
use XCart\Bus\Query\Data\ScriptStateDataSource;
use XCart\Bus\Rebuild\Executor;
use XCart\Bus\Rebuild\Executor\ScriptState;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class RebuildResolver
{
    /**
     * @var InstalledModulesDataSource
     */
    private $installedModulesDataSource;

    /**
     * @var ScriptStateDataSource
     */
    private $scriptStateDataSource;

    /**
     * @var ScenarioDataSource
     */
    private $scenarioDataSource;

    /**
     * @var SystemDataResolver
     */
    private $systemDataResolver;

    /**
     * @var Executor
     */
    private $executor;

    /**
     * @param InstalledModulesDataSource $installedModulesDataSource
     * @param ScriptStateDataSource      $scriptStateDataSource
     * @param ScenarioDataSource         $scenarioDataSource
     * @param SystemDataResolver         $systemDataResolver
     * @param Executor                   $executor
     */
    public function __construct(
        InstalledModulesDataSource $installedModulesDataSource,
        ScriptStateDataSource $scriptStateDataSource,
        ScenarioDataSource $scenarioDataSource,
        SystemDataResolver $systemDataResolver,
        Executor $executor
    ) {
        $this->installedModulesDataSource = $installedModulesDataSource;
        $this->scriptStateDataSource      = $scriptStateDataSource;
        $this->scenarioDataSource         = $scenarioDataSource;
        $this->systemDataResolver         = $systemDataResolver;
        $this->executor                   = $executor;
    }

    /**
     * @param             $value
     * @param             $args
     * @param             $context
     * @param ResolveInfo $info
     *
     * @return array
     *
     * @Resolver()
     */
    public function find($value, $args, $context, ResolveInfo $info)
    {
        $state = $this->scriptStateDataSource->find($args['id']);

        $state['gaData'] = $this->getGAData($state);

        return $state;
    }

    /**
     * @param             $value
     * @param array       $args
     * @param Context     $context
     * @param ResolveInfo $info
     *
     * @return ScriptState
     * @throws \Exception
     *
     * @Resolver()
     */
    public function startRebuild($value, $args, $context, ResolveInfo $info)
    {
        $scenario = $this->scenarioDataSource->find($args['id']);
        if (!$scenario) {
            throw new \Exception("Couldn't find request scenario");
        }

        $state = $this->executor->initializeByScenario($args['type'] ?? 'redeploy', $scenario);
        if (!$state) {
            throw new \Exception("Couldn't start the rebuild");
        }

        if (isset($args['failureReturnUrl'])) {
            $state->failureReturnUrl = $args['failureReturnUrl'];
        }

        $state->reason = $args['reason'] ?? 'redeploy';

        $this->scriptStateDataSource->saveOne($state, $state->id);

        // remove scenario, to avoid double execution
        $this->scenarioDataSource->removeOne($args['id']);

        $state['gaData'] = $this->getGAData($state);

        return $state;
    }

    /**
     * @param             $value
     * @param array       $args
     * @param Context     $context
     * @param ResolveInfo $info
     *
     * @return ScriptState
     * @throws \Exception
     *
     * @Resolver()
     */
    public function startRollback($value, $args, $context, ResolveInfo $info)
    {
        $state = $this->scriptStateDataSource->find($args['id']);
        if (!$state) {
            throw new \Exception('Could not find request state');
        }

        $type = 'rollback';
        if ($state->type === 'self-upgrade') {
            $type = 'self-rollback';
        }

        $state = $this->executor->initializeByState($type, $state);

        $this->scriptStateDataSource->saveOne($state, $state->id);

        $state['gaData'] = $this->getGAData($state);

        return $state;
    }

    /**
     * unused
     *
     * @param             $value
     * @param             $args
     * @param             $context
     * @param ResolveInfo $info
     *
     * @return ScriptState
     * @throws \Exception
     *
     * @Resolver()
     */
    public function cancelRebuild($value, $args, $context, ResolveInfo $info)
    {
        $state = $this->executor->cancel($this->getStateById($args['id']));
        $this->scriptStateDataSource->saveOne($state, $args['id']);

        $state['gaData'] = $this->getGAData($state);

        return $state;
    }

    /**
     * @param             $value
     * @param             $args
     * @param             $context
     * @param ResolveInfo $info
     *
     * @return ScriptState
     * @throws \Exception
     *
     * @Resolver()
     */
    public function executeRebuild($value, $args, $context, ResolveInfo $info)
    {
        $state = $this->getStateById($args['id']);

        try {
            $newState = $this->executor->execute(
                clone $state,
                $args['action'] ?? 'execute',
                $args['params'] ?? []
            );
            $this->scriptStateDataSource->saveOne($newState, $args['id']);

        } catch (ScriptExecutionError $e) {
            if ($e->getCode() === ScriptExecutionError::REASON_FINISHED_ALREADY) {
                return $state;
            }

            if ($e->getCode() === ScriptExecutionError::REASON_OWNED_BY_ANOTHER_USER) {
                // We're doing the long polling here for non-owning user
                sleep(1);

                // Refresh state from storage
                return $this->getStateById($args['id']);
            }

            throw $e;
        }

        $newState['gaData'] = $this->getGAData($newState);

        return $newState;
    }

    /**
     * @param             $value
     * @param             $args
     * @param             $context
     * @param ResolveInfo $info
     *
     * @return array
     * @throws \Exception
     *
     * @Resolver()
     */
    public function ongoingScripts($value, $args, $context, ResolveInfo $info)
    {
        return array_map(function (ScriptState $state) {
            $state['progress'] = (float) $state->currentStep / $state->stepsCount;

            return $state;
        }, $this->scriptStateDataSource->getRunning());
    }

    /**
     * @param string $id
     *
     * @return ScriptState
     * @throws \Exception
     */
    private function getStateById($id): ScriptState
    {
        $state = $this->scriptStateDataSource->find($id);
        if (!$state) {
            throw new \Exception("Couldn't find state");
        }

        return $state;
    }

    /**
     * @param ScriptState $state
     *
     * @return array
     */
    private function getGAData(ScriptState $state): array
    {
        /** @var Module $core */
        $core        = $this->installedModulesDataSource->find('CDev-Core');
        $coreVersion = $core ? $core->version : '';

        $newCoreVersion = '';
        if ($state->reason === 'upgrade') {
            foreach ($state->transitions as $id => $transition) {
                if ($id === 'CDev-Core') {
                    $newCoreVersion = $transition['stateAfterTransition']['version'];
                }
            }
        }

        $category = 'addon-state-change';
        $reason = '';

        if ($state->type === 'rollback' || $state->type === 'self-rollback') {
            $category = 'rollback';
            $reason = 'addon-state-change';
            if ($state->type === 'self-rollback') {
                $reason = 'self-upgrade';
            } elseif ($state->reason === 'upgrade') {
                $reason = 'upgrade';
            }
        } elseif ($state->type === 'self-upgrade') {
            $category = 'self-upgrade';
        } elseif ($state->reason === 'upgrade') {
            $category = 'upgrade';
        } elseif (empty($state->transitions)) {
            $category = 'rebuild-cache';
        }

        $data = [
            $category,
            PHP_VERSION, // PHP version
            $this->systemDataResolver->getMysqlVersion(), // MySQL version
            $coreVersion,
            $newCoreVersion,
        ];

        if ($reason) {
            $data[] = $reason;
        }

        return $data;
    }
}
