<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Scenario;

use XCart\Bus\Domain\Module;
use XCart\Bus\Query\Data\CoreConfigDataSource;
use XCart\Bus\Query\Data\InstalledModulesDataSource;
use XCart\Bus\Query\Data\MarketplaceModulesDataSource;
use XCart\Bus\Rebuild\Scenario\ScenarioRule\ScenarioRuleException;
use XCart\Bus\Rebuild\Scenario\ScenarioRule\ScenarioRuleInterface;
use XCart\Bus\Rebuild\Scenario\Transition\EnableTransition;
use XCart\Bus\Rebuild\Scenario\Transition\TransitionInterface;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class ScenarioBuilder
{
    /**
     * @var ScenarioRuleInterface[]
     */
    private $rules;

    /**
     * @var TransitionInterface[]
     */
    private $moduleTransitions = [];

    /**
     * @var InstalledModulesDataSource
     */
    private $installedModulesDataSource;

    /**
     * @param InstalledModulesDataSource   $installedModulesDataSource
     * @param MarketplaceModulesDataSource $marketplaceModulesDataSource
     * @param CoreConfigDataSource         $coreConfigDataSource
     *
     * @return static
     *
     * @Service\Constructor
     * @codeCoverageIgnore
     */
    public static function serviceConstructor(
        InstalledModulesDataSource $installedModulesDataSource,
        MarketplaceModulesDataSource $marketplaceModulesDataSource,
        CoreConfigDataSource $coreConfigDataSource
    ) {
        return new self(
            [
                new ScenarioRule\Dependencies\InstallNotInstalled(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource
                ),
                new ScenarioRule\Dependencies\UpgradeDependency(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource
                ),
                new ScenarioRule\Dependencies\CoreVersion(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource,
                    $coreConfigDataSource
                ),
                new ScenarioRule\Dependencies\EnableNotEnabled(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource
                ),
                new ScenarioRule\Dependencies\ForceEnabledWhenRequired(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource
                ),
                new ScenarioRule\Dependencies\ForceDisabledWhenIncompatible(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource
                ),
                new ScenarioRule\Dependencies\ForceSystemDisabledIfIncompatible(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource
                ),
                new ScenarioRule\Dependencies\DisableNonRelatedSkins(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource
                ),
                new ScenarioRule\CoreVersion(
                    $installedModulesDataSource,
                    $marketplaceModulesDataSource,
                    $coreConfigDataSource
                ),
            ],
            $installedModulesDataSource
        );
    }

    /**
     * @param ScenarioRuleInterface[]    $rules
     * @param InstalledModulesDataSource $installedModulesDataSource
     */
    public function __construct(array $rules, InstalledModulesDataSource $installedModulesDataSource)
    {
        $this->rules                      = $rules;
        $this->installedModulesDataSource = $installedModulesDataSource;

        //$this->addSystemTransitions();
    }

    /**
     * @param TransitionInterface $transition
     *
     * @throws ScenarioRule\ScenarioRuleException
     */
    public function addTransition(TransitionInterface $transition): void
    {
        if (isset($this->moduleTransitions[$transition->getModuleId()])) {
            $transition = $this->moduleTransitions[$transition->getModuleId()];
        }

        $rules = $this->getRules();

        foreach ($rules as $rule) {
            if ($rule->isApplicable($transition)) {
                $rule->applyTransform(
                    $transition,
                    $this
                );
            }
        }

        $this->moduleTransitions[$transition->getModuleId()] = $transition;
    }

    /** @noinspection PhpDocRedundantThrowsInspection */

    /**
     * @return TransitionInterface[]
     *
     * @throws ScenarioRuleException
     */
    public function getTransitions(): array
    {
        $rules = $this->getRules();

        return array_filter(
            $this->moduleTransitions,
            function ($transition) use ($rules) {
                foreach ($rules as $name => $rule) {
                    if ($rule->isApplicable($transition)) {
                        try {
                            $rule->applyFilter($transition, $this);

                        } catch (ScenarioRuleException $exception) {
                            if ($exception->getCode() === ScenarioRuleException::SOFT_EXCEPTION) {
                                return false;
                            }

                            throw $exception;
                        }
                    }
                }

                return true;
            }
        );
    }

    /**
     * @param string $id
     *
     * @return TransitionInterface|null
     */
    public function getTransition($id): ?TransitionInterface
    {
        return $this->moduleTransitions[$id] ?? null;
    }

    /**
     * @param string $id
     *
     * @return boolean
     */
    public function removeTransition($id): bool
    {
        if (isset($this->moduleTransitions[$id])) {
            unset($this->moduleTransitions[$id]);

            return true;
        }

        return false;
    }

    /**
     * @return ScenarioRuleInterface[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param TransitionInterface $transition
     *
     * @return TransitionInterface
     */
    public function fillSystemTransitionInfo(TransitionInterface $transition): TransitionInterface
    {
        $info = new TransitionInfo();
        $info->setReason('system');

        $transition->setInfo($info);

        return $transition;
    }

    /**
     * Adds system modules transitions
     * @throws ScenarioRuleException
     */
    public function addSystemTransitions(): void
    {
        $transitions = [];

        $modules                = $this->installedModulesDataSource->getAll();
        $installedSystemModules = array_filter($modules, function ($module) {
            /** @var Module $module */
            return !empty($module->isSystem);
        });

        foreach ($installedSystemModules as $systemModuleId => $systemModule) {
            if ($systemModuleId === 'CDev-Core') {
                continue;
            }

            if ($systemModuleId === 'XC-Service') {
                continue;
            }

            if (!isset($this->moduleTransitions[$systemModuleId])) {
                $transition    = new EnableTransition($systemModuleId);
                $transitions[] = $this->fillSystemTransitionInfo($transition);
            }
        }

        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }
    }
}
