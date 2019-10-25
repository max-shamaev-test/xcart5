<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\View\ItemsList\Model\Payment\Item;

class PaymentMethod extends \XLite\View\ItemsList\Model\Payment\Item\PaymentMethod implements \XLite\Base\IDecorator
{
    /**
     * @return string
     */
    public function getAdminIconURL()
    {
        $result = parent::getAdminIconURL();

        $method = $this->getPayment();

        if ($method->isLegacyXpaymentsMethod()) {
            $result = $method->getIconURL() ?: $result;
        }

        return $result;
    }

}
