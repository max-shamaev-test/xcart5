<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Controller\Customer;

/**
 * Checkout controller
 */
class Checkout extends \XLite\Controller\Customer\Checkout implements \XLite\Base\IDecorator
{
    /**
     * Send event in case if Anonymous customer intended to create profile
     *
     * @throws \Exception
     */
    protected function updateAnonymousProfile()
    {
        parent::updateAnonymousProfile();

        $createProfile = (
            \XLite\Core\Session::getInstance()->order_create_profile
            && \XLite\Core\Session::getInstance()->createProfilePassword
        );

        \XLite\Core\Event::xpaymentsAnonymousRegister(
            array(
                'value' => $createProfile,
            )
        );
    }

    /**
     * Checks if customer is Anonymous and didn't choose to create profile
     */
    public function isAnonymousNotRegisters()
    {
        return $this->isAnonymous()
            && (
                !\XLite\Core\Session::getInstance()->order_create_profile
                || !\XLite\Core\Session::getInstance()->createProfilePassword
            );
    }

    /**
     * Returns xpaymentsCustomerId for current profile (if available)
     */
    public function getXpaymentsCustomerId()
    {
        return ($this->getCart()->getProfile())
            ? $this->getCart()->getProfile()->getXpaymentsCustomerId()
            : '';
    }

    /**
     * Sends updated total on cartUpdate (not only the difference)
     *
     * @return boolean
     */
    protected function assembleEvent()
    {
        $result = parent::assembleEvent();
        if ($result) {
            \XLite\Core\Event::xpaymentsTotalUpdate(
                array(
                    'total' => $this->getCart()->getTotal(),
                    'currency' => $this->getCart()->getCurrency()->getCode(),
                )
            );
        }
        return $result;
    }

}
