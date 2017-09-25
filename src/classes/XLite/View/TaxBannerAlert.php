<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\View;

use XLite\Core\Promo;
use XLite\Module\XC\TaxJar;

/**
 * Tax banner alert widget
 *
 * @ListChild (list="taxes.top.section", zone="admin", weight="10")
 */
class TaxBannerAlert extends \XLite\View\ModuleBanner
{
    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'tax_classes';
        $result[] = 'sales_tax';
        $result[] = 'vat_tax';
        $result[] = 'canadian_taxes';

        return $result;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'tax_banner_alert/style.less';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'tax_banner_alert/body.twig';
    }

    /**
     * Get module name
     *
     * @return string
     */
    protected function getModuleName()
    {
        return 'XC\\TaxJar';
    }

    /**
     * Get logo url
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return \XLite\Core\Layout::getInstance()->getResourceWebPath(
            'tax_banner_alert/taxjar_logo.png'
        );
    }

    /**
     * Get module id
     *
     * @return string
     */
    protected function getModuleId()
    {
        return $this->isModuleInstalled() && $this->getModule() ? $this->getModule()->getModuleId() : '';
    }

    /**
     * Get module id
     *
     * @return \XLite\Model\Module
     */
    protected function getModule()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Module')->findOneByModuleName($this->getModuleName());
    }

    public function isEnableCheckboxVisible()
    {
        return $this->isModuleInstalled() && $this->isModuleEnabled()
            && TaxJar\Main::isConfigured();
    }

    public function isJoinButtonVisible()
    {
        return $this->isModuleInstalled() && $this->isModuleEnabled()
            && !TaxJar\Main::isConfigured();
    }

    public function getTaxJarEnabledValue()
    {
        return \XLite\Core\Config::getInstance()->XC->TaxJar->taxcalculation;
    }

    public function getJoinLink()
    {
        return 'https://www.taxjar.com/xcart-sales-tax/?utm_source=xcart-module';
    }

    public function getModuleLink()
    {
        return $this->getModule() && $this->getModule()->getEnabled()
            ? $this->getModule()->getSettingsForm()
            : $this->getInstallLink();
    }

    public function isInstallButtonVisible()
    {
        return !$this->isModuleInstalled();
    }

    public function getInstallLink()
    {
        return Promo::getInstance()->getRecommendedModuleURL($this->getModuleName());
    }

    public function isDashboardButtonVisible()
    {
        return \XLite\Core\Config::getInstance()->XC->TaxJar->api_key;
    }

    public function getDashboardLink()
    {
        return 'https://app.taxjar.com/?utm_source=xcart-module';
    }

    protected function isModuleEnabled()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Module')->isModuleEnabled($this->getModuleName());
    }

    /**
     * Returns current target
     *
     * @return string
     */
    protected function getCurrentTarget()
    {
        return \XLite\Core\Request::getInstance()->target;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return \XLite\View\AView::isVisible()
            && \XLite\Controller\Admin\TaxClasses::isEnabled();
    }
}
