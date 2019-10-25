<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Scenario\ChangeUnitBuildRule;

use XCart\Bus\Domain\Module;
use XCart\Bus\Query\Data\InstalledModulesDataSource;
use XCart\Bus\Query\Data\MarketplaceModulesDataSource;
use XCart\Bus\Rebuild\Scenario\Transition\InstallDisabledTransition;
use XCart\Bus\Rebuild\Scenario\Transition\InstallEnabledTransition;
use XCart\Bus\Rebuild\Scenario\Transition\TransitionInterface;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class Install implements ChangeUnitBuildRuleInterface
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
     * @param InstalledModulesDataSource   $installedModulesDataSource
     * @param MarketplaceModulesDataSource $marketplaceModulesDataSource
     */
    public function __construct(
        InstalledModulesDataSource $installedModulesDataSource,
        MarketplaceModulesDataSource $marketplaceModulesDataSource
    ) {
        $this->installedModulesDataSource   = $installedModulesDataSource;
        $this->marketplaceModulesDataSource = $marketplaceModulesDataSource;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'install';
    }

    /**
     * @param array $changeUnit
     *
     * @return bool
     */
    public function isApplicable(array $changeUnit): bool
    {
        return isset($changeUnit['install'])
            && $changeUnit['install'] === true
            && (!empty($changeUnit['version']) || !empty($changeUnit['installLatestVersion']))
            && $this->marketplaceModulesDataSource->find($changeUnit['id'])
            && !$this->installedModulesDataSource->find($changeUnit['id']);

    }

    /**
     * @param array $transitions
     *
     * @return bool
     */
    public function isApplicableWithOthers(array $transitions): bool
    {
        return true;
    }

    /**
     * @param array $changeUnit
     *
     * @return TransitionInterface|null
     */
    public function build(array $changeUnit): ?TransitionInterface
    {
        $id      = $changeUnit['id'];
        $enabled = isset($changeUnit['enable'])
            ? (bool) $changeUnit['enable']
            : true;

        if (!empty($changeUnit['installLatestVersion'])) {
            /** @var Module[] $module */
            $module = $this->marketplaceModulesDataSource->find($id);
            $module = array_pop($module);

            $changeUnit['version'] = $module->version;
        }

        return $enabled
            ? new InstallEnabledTransition($id, $changeUnit['version'])
            : new InstallDisabledTransition($id, $changeUnit['version']);
    }
}
