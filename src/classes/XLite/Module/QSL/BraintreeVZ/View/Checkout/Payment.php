<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\BraintreeVZ\View\Checkout;

/**
 * Checkout payment
 *
 */
abstract class Payment extends \XLite\View\Checkout\Payment implements \XLite\Base\IDecorator
{
    /**
     * Checks if fastlane checkout mode is enabled
     *
     * @return boolean
     */
    protected function isFastlaneEnabled()
    {
        return 'fast-lane' === \XLite\Core\Config::getInstance()->General->checkout_type;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getBraintreeSkinDir() . 'style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $client = \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance();

        $list = parent::getJSFiles();

        $list[] = array(
            'file'      => $this->getBraintreeSkinDir() . 'braintree_payment.min.js',
            'no_minify' => true,
        );

        $list[] = array(
            'file'      => $this->getBraintreeSkinDir() . 'checkout.min.js',
            'no_minify' => true,
        );

        if ($this->isFastlaneEnabled()) {
            $list[] = $this->getBraintreeSkinDir() . 'flc.js';
        } else {
            $list[] = $this->getBraintreeSkinDir() . 'opc.js';
        }

        $list[] = array(
            'url'   => 'https://js.braintreegateway.com/web/3.19.0/js/client.min.js',
            'async' => true,
        );

        $list[] = array(
            'url'   => 'https://js.braintreegateway.com/web/3.19.0/js/hosted-fields.min.js',
            'async' => true,
        );

        if ('1' == $client->getSetting('isPayPal')) {

            $list[] = array(
                'url'   => 'https://js.braintreegateway.com/web/3.19.0/js/paypal.min.js',
                'async' => true,
            );

            $list[] = array(
                'url'   => 'https://js.braintreegateway.com/web/3.19.0/js/paypal-checkout.min.js',
                'async' => true,
            );

            $list[] = array(
                'url'   => 'https://www.paypalobjects.com/api/checkout.js',
                'async' => true,
            );
        }

        if ('1' == $client->getSetting('is3dSecure')) {

            $list[] = array(
                'url'   => 'https://js.braintreegateway.com/web/3.19.0/js/three-d-secure.min.js',
                'async' => true,
            );
        }

        return $list;
    }

    /**
     * Skin directory for checkout 
     *
     * @return string
     */
    protected function getBraintreeSkinDir()
    {
        return 'modules/QSL/BraintreeVZ/checkout/';
    }

    /**
     * Payment method ID
     *
     * @return int
     */
    public function getPaymentId()
    {
        return \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance()->getPaymentMethod()->getMethodId();
    }

    /**
     * Check if PayPal is allowed
     *
     * @return bool
     */
    protected function isPayPalAllowed()
    {
        return '1' == \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance()->getSetting('isPayPal');
    }

    /**
     * Check if customer has saved credit cards
     *
     * @return bool
     */
    protected function hasCreditCards()
    {
        $creditCards = $this->getCart()->getProfile()->getBraintreeCreditCardsHash();

        return !empty($creditCards);
    }

    /**
     * Show save card checkbox on checkout
     *
     * @return bool
     */
    public function isShowSaveCardBox()
    {
        return \XLite\Core\Auth::getInstance()->isLogged()
            && \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance()->getSetting('isUseVault')
            && (bool)\XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance()->getSetting('isShowSaveCardBox');
    }

    /**
     * Is save card checkbox checked
     *
     * @return bool
     */
    public function isSaveCardBoxChecked()
    {
        return \XLite\Core\Auth::getInstance()->isLogged()
            && \XLite\Core\Auth::getInstance()->getProfile()->getSaveCardBoxChecked();
    }

    /**
     * Get nonce from Session   
     * 
     * @return string
     */
    public function getSessionNonce()
    {
        return strval(\XLite\Core\Session::getInstance()->braintree_paypal_nonce);
    }
}
