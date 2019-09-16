<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\View\Menu\Admin\Info\Node;

use XLite\Core\Auth;
use XLite\Model\Role\Permission;
use \XLite\Core\URLManager;
use Includes\Utils\Module\Manager;

/**
 * Google ads node
 */
class GoogleAds extends \XLite\View\Menu\Admin\ANodeNotification
{
    /**
     * Check if data is updated (must be fast)
     *
     * @return boolean
     */
    public function isUpdated()
    {
        return $this->getLastReadTimestamp() < $this->getLastUpdateTimestamp();
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getUnreadCount() !== 0
            && $this->getCompletedOrdersCount() >= 15;
    }

    /**
     * @return bool
     */
    protected function checkACL()
    {
        return parent::checkACL() && Auth::getInstance()->isPermissionAllowed(Permission::ROOT_ACCESS);
    }

    /**
     * Check if Google Ads module is enabled or not
     */
    protected function isGoogleAdsEnabled()
    {
        return  Manager::getRegistry()->isModuleEnabled('Kliken', 'GoogleAds');
    }


    /**
     * Return update timestamp
     *
     * @return integer
     */
    protected function getLastUpdateTimestamp()
    {
        $result = \XLite\Core\TmpVars::getInstance()->googleAdsInfoTimestamp;

        if (null === $result) {
            $result = LC_START_TIME;
            $this->setLastUpdateTimestamp($result);
        }

        return $result;
    }

    /**
     * Set update timestamp
     *
     * @param integer $timestamp Timestamp
     *
     * @return void
     */
    protected function setLastUpdateTimestamp($timestamp)
    {
        \XLite\Core\TmpVars::getInstance()->googleAdsInfoTimestamp = $timestamp;
    }

    /**
     * Returns node style class
     *
     * @return array
     */
    protected function getNodeStyleClasses()
    {
        $list   = parent::getNodeStyleClasses();
        $list[] = 'google-ads';

        return $list;
    }

    /**
     * Returns icon
     *
     * @return string
     */
    protected function getIcon()
    {
        return $this->getSVGImage('images/info.svg');
    }

    /**
     * Returns header url
     *
     * @return string
     */
    protected function getHeaderUrl()
    {
        return $this->isGoogleAdsEnabled()
            ? $this->buildURL(\XLite\Module\Kliken\GoogleAds\Logic\Helper::PAGE_SLUG)
            : URLManager::appendParamsToUrl(
                'https://market.x-cart.com/addons/google-ads-for-xcart.html',
                [
                    'utm_source' => 'xc5admin',
                    'utm_medium'    => 'notification',
                    'utm_campaign'  => 'XC5admin'
                ]
            );
    }

    /**
     * Returns header
     *
     * @return string
     */
    protected function getHeader()
    {
        return static::t('Insight: Boost your sales with Google Ads addon');
    }

    /**
     * Returns completed orders count
     *
     * @return int
     */
    protected function getCompletedOrdersCount() {
        return \XLite\Core\Database::getRepo('XLite\Model\Order')->getCompletedOrdersCount();
    }

    /**
     * Check if target is blank or not
     *
     * @return boolean
     */
    protected function targetIsBlank()
    {
        return true;
    }
}
