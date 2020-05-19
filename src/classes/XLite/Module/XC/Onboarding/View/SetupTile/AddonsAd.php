<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Onboarding\View\SetupTile;

use XLite\Core\Layout;

/**
 * SetupTile
 *
 * @ListChild(list="onboarding.setup_tiles", weight="110", zone="admin")
 */
class AddonsAd extends \XLite\Module\XC\Onboarding\View\SetupTile\ASetupTile
{
    protected function getContentText()
    {
        return static::t('What about adding some marketing magic to your sales process?');
    }

    protected function getImage()
    {
        $imageUrl = Layout::getInstance()->getResourceWebPath(
            'modules/XC/Onboarding/images/addons-ad.png',
            Layout::WEB_PATH_OUTPUT_URL
        );

        return '<img src="' . $imageUrl . '">';
    }

    protected function getButtonLabel()
    {
        return static::t('App store');
    }

    protected function getButtonURL()
    {
        return \XLite::getInstance()->getServiceURL('#/marketplace');
    }
    
    /**
     * @return string
     */
    protected function getClass()
    {
        return parent::getClass() . ' onboarding-tile-hidden';
    }

    protected function getButtonConciergeLinkTitle()
    {
        return 'Concierge: Add-ons ad Dashboard';
    }

    protected function getCloseConciergeLinkTitle()
    {
        return 'Concierge: Add-ons ad Dashboard closed';
    }
}