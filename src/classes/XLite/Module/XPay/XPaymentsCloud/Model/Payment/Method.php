<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Model\Payment;

/**
 * Payment method
 */
class Method extends \XLite\Model\Payment\Method implements \XLite\Base\IDecorator
{
    /**
    * Get method_id
    *
    * @return integer
    */
    public function getMethodId()
    {
        return (
            $this->isLegacyXpaymentsMethod()
            && $this->getXpaymentsPaymentMethod()
            && $this->getFromMarketplace()
        )
            ? $this->getXpaymentsPaymentMethod()->getMethodId()
            : parent::getMethodId();
    }

    /**
     * Get added
     *
     * @return bool
     */
    public function getAdded()
    {
        $result = parent::getAdded();

        if ($this->isLegacyXpaymentsMethod()) {
            if ($this->getXpaymentsPaymentMethod()->getAdded()) {
                $result = true;
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Returns true if it is an old X-Payments payment method
     *
     * @return bool
     */
    public function isLegacyXpaymentsMethod()
    {
        return false !== strpos($this->getServiceName(), 'XPayments.Allowed')
            || false !== strpos($this->getServiceName(), 'SavedCard');
    }

    /**
    * Returns X-Payments Cloud payment method
    *
    * @return \XLite\Model\Payment\Method
    */
    protected function getXpaymentsPaymentMethod()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Payment\Method')
            ->findOneBy(['service_name' => 'XPaymentsCloud']);
    }

}
