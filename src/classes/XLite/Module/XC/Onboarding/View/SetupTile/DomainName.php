<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Onboarding\View\SetupTile;

use XLite\Module\XC\Onboarding\Main;

/**
 * SetupTile
 *
 * @ListChild(list="onboarding.setup_tiles", weight="30", zone="admin")
 */
class DomainName extends \XLite\Module\XC\Onboarding\View\SetupTile\ASetupTile
{
    protected function getHeader()
    {
        return static::t('Domain name');
    }

    protected function getContentText()
    {
        return static::t('Your current domain is X', ['domain' => Main::getCloudDomainName()]);
    }

    protected function getImage()
    {
        return $this->getSVGImage('modules/XC/Onboarding/images/tile-domain.svg');
    }

    protected function getButtonLabel()
    {
        return static::t('Change');
    }

    protected function getButtonURL()
    {
        return $this->buildURL('cloud_domain_name');
    }

    protected function getButtonConciergeLinkTitle()
    {
        return 'Concierge: Onboarding Step Domain Dashboard';
    }

    protected function getCloseConciergeLinkTitle()
    {
        return 'Concierge: Onboarding Step Domain Dashboard closed';
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        return !\XLite::getInstance()->getOptions(['service', 'is_trial']);
    }
}