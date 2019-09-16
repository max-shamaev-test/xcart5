<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\PINCodes\View\Popup;


class BackstockStatusChangeNotification extends \XLite\View\Popup\BackstockStatusChangeNotification implements \XLite\Base\IDecorator
{
    public function getCSSFiles()
    {
        return array_merge(parent::getCSSFiles(), [
            'modules/CDev/PINCodes/popup/backstock_status_change_notification/style.css',
        ]);
    }
}