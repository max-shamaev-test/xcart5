<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Onboarding\View\SetupTile;

/**
 * SetupTile
 *
 * @ListChild(list="onboarding.setup_tiles", weight="20", zone="admin")
 */
class ShippingMethods extends \XLite\Module\XC\Onboarding\View\SetupTile\ASetupTile
{
    protected function getHeader()
    {
        return static::t('Shipping');
    }

    protected function getContentText()
    {
        return static::t('Onboarding: Get shipping rates from major shipping carrier companies.');
    }

    protected function getImage()
    {
        return $this->getSVGImage('modules/XC/Onboarding/images/tile-shipping.svg');
    }

    protected function getButtonLabel()
    {
        return static::t('Set it up');
    }

    protected function getButtonURL()
    {
        return $this->buildURL('shipping_methods', '', ['show_add_shipping_popup' => 1]);
    }

    protected function getButtonConciergeLinkTitle()
    {
        return 'Concierge: Onboarding Step Shipping Dashboard';
    }

    protected function getCloseConciergeLinkTitle()
    {
        return 'Concierge: Onboarding Step Shipping Dashboard closed';
    }
}