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
 * @ListChild(list="onboarding.setup_tiles", weight="10", zone="admin")
 */
class PaymentMethods extends \XLite\Module\XC\Onboarding\View\SetupTile\ASetupTile
{
    protected function getHeader()
    {
        return static::t('Payment Processing');
    }

    protected function getContentText()
    {
        return static::t('Choose the best way for customers to pay you.');
    }

    protected function getImage()
    {
        return $this->getSVGImage('modules/XC/Onboarding/images/tile-payments.svg');
    }

    protected function getButtonLabel()
    {
        return static::t('Set it up');
    }

    protected function getButtonURL()
    {
        return $this->buildURL('payment_settings', '', ['show_add_payment_popup' => 1]);
    }

    protected function getButtonConciergeLinkTitle()
    {
        return 'Concierge: Onboarding Step Payment Dashboard';
    }

    protected function getCloseConciergeLinkTitle()
    {
        return 'Concierge: Onboarding Step Payment Dashboard closed';
    }
}