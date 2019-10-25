<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\View\Tabs;

/**
 * Profile dialog
 */
class AdminProfile extends \XLite\View\Tabs\AdminProfile implements \XLite\Base\IDecorator
{
    /**
     * Returns the list of targets where this widget is available
     *
     * @return string[]
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = 'xpayments_cards';

        return $list;
    }

    /**
     * Get customer profile
     *
     * @return \XLite\Model\Profile
     */
    protected function getCustomerProfile()
    {
        $profileId = \XLite\Core\Request::getInstance()->profile_id;
        if (empty($profileId)) {
            $profileId = \XLite\Core\Auth::getInstance()->getProfile()->getProfileId();
        }

        return \XLite\Core\Database::getRepo('XLite\Model\Profile')
            ->find(intval($profileId));
    }

    /**
     * Define tabs
     *
     * @return array
     */
    protected function defineTabs()
    {
        $tabs = parent::defineTabs();

        if (!$this->getCustomerProfile()->getAnonymous()) {
            $tabs['xpayments_cards'] = array(
                'weight'   => 1100,
                'title'    => static::t('Saved cards'),
                'template' => 'modules/XPay/XPaymentsCloud/account/xpayments_cards.twig',
            );
        }

        return $tabs;
    }

    /**
     * Register files from common repository
     *
     * @return array
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        $list['css'][] = 'modules/XPay/XPaymentsCloud/account/cc_type_sprites.css';
        $list['css'][] = 'modules/XPay/XPaymentsCloud/account/style.css';

        return $list;
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/XPay/XPaymentsCloud/account/style.css';

        return $list;
    }
}
