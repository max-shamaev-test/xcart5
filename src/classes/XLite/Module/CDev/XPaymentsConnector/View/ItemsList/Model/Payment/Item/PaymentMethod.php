<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\View\ItemsList\Model\Payment\Item;

use XLite\Module\CDev\XPaymentsConnector\Core\Settings;

class PaymentMethod extends \XLite\View\ItemsList\Model\Payment\Item\PaymentMethod implements \XLite\Base\IDecorator
{
    /**
     * @return string
     */
    public function getAdminIconURL()
    {
        $method = $this->getPayment();

        if (Settings::XP_MODULE_NAME === $method->getModuleName()) {
            $url = $method->getAdminIconURL();

            if (
                !$url
                && $method->isModuleInstalled()
                && !$method->isModuleEnabled()
            ) {
                $url = \XLite\Core\Layout::getInstance()
                    ->getResourceWebPath('modules/CDev/XPaymentsConnector/method_icon_xp.png');
            }

        } else {
            $url = parent::getAdminIconURL();
        }

        return $url;
    }
}
