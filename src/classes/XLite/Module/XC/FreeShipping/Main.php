<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\FreeShipping;

use XLite\Module\XC\FreeShipping\Core\MethodsLoader;

/**
 * Main module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'X-Cart team';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Free Shipping and Shipping freights';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Attract more customers by offering Free Shipping. Mark the products that are shipped for free. Add a possibility to specify shipping freight to products.';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.3';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '5';
    }

    /**
     * Get module build number (4th number in the version)
     *
     * @return string
     */
    public static function getBuildVersion()
    {
        return '3';
    }

    /**
     * Get minorRequiredCoreVersion
     *
     * @return integer
     */
    public static function getMinorRequiredCoreVersion()
    {
        return '5';
    }

    /**
     * Display settings form
     *
     * @return boolean
     */
    public static function showSettingsForm()
    {
        return true;
    }

    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        MethodsLoader::process();
        static::switchFreeShippingMethods(true);
    }

    /**
     * Method to call just before the module is uninstalled via core
     *
     * @return void
     */
    public static function callUninstallEvent()
    {
        parent::callUninstallEvent();

        static::removeFreeShippingMethods();
    }

    /**
     * Method to call just before the module is disabled via core
     *
     * @return void
     */
    public static function callDisableEvent()
    {
        parent::callDisableEvent();

        static::switchFreeShippingMethods(false);
    }

    /**
     * Remove service free shipping modules
     */
    protected static function removeFreeShippingMethods()
    {
        $shippingMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->findBy(['code' => 'FREESHIP']);
        foreach ($shippingMethods as $method) {
            \XLite\Core\Database::getEM()->remove($method);
        }

        $shippingMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->findBy(['code' => 'FIXEDFEE']);
        foreach ($shippingMethods as $method) {
            \XLite\Core\Database::getEM()->remove($method);
        }

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Switch service free shipping modules
     *
     * @param bool $enabled enabled
     */
    protected static function switchFreeShippingMethods($enabled)
    {
        $shippingMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->findBy(['code' => 'FREESHIP']);
        foreach ($shippingMethods as $method) {
            $method->setEnabled($enabled);
        }

        $shippingMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->findBy(['code' => 'FIXEDFEE']);
        foreach ($shippingMethods as $method) {
            $method->setEnabled($enabled);
        }

        \XLite\Core\Database::getEM()->flush();
    }
}
