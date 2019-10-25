<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\View\Checkout;

use XLite\Module\XPay\XPaymentsCloud\Model\Payment\Processor\XPaymentsCloud;

/**
 * Payment template
 */
abstract class Payment extends \XLite\View\Checkout\Payment implements \XLite\Base\IDecorator
{
    /**
     * Get CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        if ($this->isXpaymentsMethodAvailable()) {
            $list[] = 'modules/XPay/XPaymentsCloud/widget.css';
        }
        return $list;
    }

    /**
     * Register files from common repository
     *
     * @return array
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();
        if ($this->isXpaymentsMethodAvailable()) {
            $list[static::RESOURCE_JS][] = 'modules/XPay/XPaymentsCloud/widget.js';
        }
        return $list;
    }

    /**
     * Checks if X-Payments Cloud method is available in checkout
     *
     * @return bool
     */
    public function isXpaymentsMethodAvailable()
    {
        static $result = null;

        if (is_null($result)) {
            $result = false;
            foreach ($this->getCart()->getPaymentMethods() as $method) {
                if ('Module\XPay\XPaymentsCloud\Model\Payment\Processor\XPaymentsCloud' == $method->getClass()) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

}
