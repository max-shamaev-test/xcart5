<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Controller\Admin;

use XCart\Marketplace\Constant;

/**
 * License keys notice page controller
 * @deprecated move logic to BUS
 */
class KeysNotice extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Do action 'Re-check'
     *
     * @return void
     */
    protected function doActionRecheck()
    {
        // todo: reimplement with BUS
        // Clear cahche of some marketplace actions
        //\XLite\Core\Marketplace::getInstance()->clearActionCache(
        //    array(
        //        Constant::REQUEST_UPDATES,
        //        Constant::REQUEST_CHECK_ADDON_KEY,
        //        Constant::INACTIVE_KEYS,
        //    )
        //);
        //
        //\XLite\Core\Marketplace::getInstance()->getAddonsList(0);

        $returnUrl = \XLite\Core\Request::getInstance()->returnUrl ?: $this->buildURL('main');

        $this->setReturnURL($returnUrl);
    }
}
