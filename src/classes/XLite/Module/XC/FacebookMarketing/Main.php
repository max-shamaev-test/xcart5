<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\FacebookMarketing;

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
        return '1';
    }

    /**
     * Get module build number (4th number in the version)
     *
     * @return string
     */
    public static function getBuildVersion()
    {
        return '1';
    }

    /**
     * Get minor core version which is required for the module activation
     *
     * @return string
     */
    public static function getMinorRequiredCoreVersion()
    {
        return '3';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Facebook Ads and Instagram Ads';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'This add-on syncs your X-Cart and your Facebook catalogs to help you make maximum use of Facebook Marketing tools having your products promoted to the right audience in the right place at the right time.';
    }

    /**
     * Determines if we need to show settings form link
     *
     * @return boolean
     */
    public static function showSettingsForm()
    {
        return true;
    }

    /**
     * Return link to settings form
     *
     * @return string
     */
    public static function getSettingsForm()
    {
        return \XLite\Core\Converter::buildURL('facebook_marketing');
    }

    /**
     * Check if Facebook Pixel enabled
     *
     * @return bool
     */
    public static function isPixelEnabled()
    {
        return (boolean)\XLite\Core\Config::getInstance()->XC->FacebookMarketing->pixel_id;
    }
}
