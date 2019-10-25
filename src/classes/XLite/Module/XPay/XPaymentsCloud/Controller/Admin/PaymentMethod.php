<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Controller\Admin;

/**
 * X-Payments Cloud connector
 *
 */
class PaymentMethod extends \XLite\Controller\Admin\PaymentMethod implements \XLite\Base\IDecorator
{

    /**
     * Checks if just_added flag is set
     *
     * @return bool
     */
    public function getXpaymentsJustAdded()
    {
        return (bool)\XLite\Core\Request::getInstance()->just_added;
    }

    /**
     * Save connect settings
     *
     * @return void
     * @throws \Exception
     */
    protected function doActionUpdate()
    {
        $method = $this->getPaymentMethod();

        $wasConfigured = $method->isConfigured();

        parent::doActionUpdate();

        \XLite\Core\TopMessage::getInstance()->clearAJAX();

        if ($wasConfigured != $method->isConfigured()) {
            \XLite\Core\Event::xpaymentsReloadPaymentStatus();
        }
    }

}
