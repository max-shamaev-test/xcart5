<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */
 
 namespace XLite\Module\XPay\XPaymentsCloud\View\Tabs;

/**
 * X-Payments Saved Cards tab
 */
class Account extends \XLite\View\Tabs\Account implements \XLite\Base\IDecorator
{
    /**
     * Returns the list of targets where this widget is available
     *
     * @return void
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = 'xpayments_cards';

        return $list;
    }

    /**
     * Define tabs
     *
     * @return array
     */
    protected function defineTabs()
    {
        $tabs = parent::defineTabs();

        if (
            $this->getProfile()
        ) {
            $tabs['xpayments_cards'] = array(
                 'weight'   => 1200,
                 'title'    => 'Saved cards',
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
