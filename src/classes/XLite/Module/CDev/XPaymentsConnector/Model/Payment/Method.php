<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\Model\Payment;

class Method extends \XLite\Model\Payment\Method implements \XLite\Base\IDecorator
{
    /**
     * The moduleEnabled field is assumed as always true for XP payment methods
     *
     * @return boolean
     */
    public function getModuleEnabled()
    {
        $classes = [
            'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments',
            'Module\CDev\XPaymentsConnector\Model\Payment\Processor\SavedCard',
        ];

        if (in_array($this->getClass(), $classes)) {
            $result = true;
        } else {
            $result = parent::getModuleEnabled();
        }

        return $result;
    }
}
