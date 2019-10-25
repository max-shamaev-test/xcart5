<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\BraintreeVZ\Controller\Customer;

use XLite\Core\Converter;

/**
 * PayPal powered by Braintree module's controller for some AJAX 
 *
 */
class Braintree extends \XLite\Controller\Customer\Checkout
{
    /**
     * Get Braintree JSON data
     *
     * @return void
     */
    protected function doActionGetBraintreeData()
    {
        $client = \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance();

        $merchantAccountId = $client->getSetting('merchantAccountId');

        if (!empty($merchantAccountId)) {

            // Use the specific merchant account ID if it's defined
            $merchantId = $client->getSetting('merchantAccountId');

        } else {

            // Use the default merchant ID
            $merchantId = $client->getSetting('merchantId');
        }

        $isButton = !empty(\XLite\Core\Request::getInstance()->is_button);

        $data = array(
            'token'              => $client->getToken(),
            'merchantId'         => $merchantId,
            'isTestMode'         => ('1' == $client->getSetting('testMode')),
            'isPayPal'           => ('1' == $client->getSetting('isPayPal')),
            'is3dSecure'         => ('1' == $client->getSetting('is3dSecure')),
            'isAcceptNo3dSecure' => ('1' == $client->getSetting('isAcceptNo3dSecure')),
            'is3dSecureForVault' => ('1' == $client->getSetting('is3dSecureForVault')),
            'kountMerchantId'    => $client->getSetting('kountMerchantId'),
            'nonceSelector'      => '#payment-method-nonce',
            'formSelector'       => ($isButton ? '.braintree-nonce-form' : 'form.place'),
            'numberSelector'     => '#braintree-card-number',
            'cvvSelector'        => '#braintree-cvv',
            'dateSelector'       => '#braintree-expiration-date',
            'isButton'           => $isButton,
            'isAnonymous'        => $this->isAnonymous(),
            'paypalButtonStyle'  => array(
                'size'  => 'responsive',
                'color' => $client->getSetting('paypalButtonColor'),
                'shape' => $client->getSetting('paypalButtonShape'),
            ),
        );

        $this->displayJSON($data);
        $this->setSuppressOutput(true);
        $this->set('silent', true);
    }

    /**
     * Get cart total via JSON
     *
     * @return void
     */
    protected function doActionGetCartTotal()
    {
        $data = [
            'total'   => Converter::preparePaypalPrice($this->getCart()->getTotal()),
        ];

        $data = array_merge($data, $this->get3dSecureParameters());

        $this->displayJSON($data);
        $this->setSuppressOutput(true);
        $this->set('silent', true);
    }

    /**
     * Get saved card nonce via JSON
     *
     * @return void
     */
    protected function doActionGetSavedCardNonce()
    {
        $client = \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance();

        $data = [
            'total' => Converter::preparePaypalPrice($this->getCart()->getTotal()),
            'nonce' => $client->getSavedCardNonce(\XLite\Core\Request::getInstance()->token),
        ];

        $data = array_merge($data, $this->get3dSecureParameters());

        $this->displayJSON($data);
        $this->setSuppressOutput(true);
        $this->set('silent', true);
    }

    /**
     * Get JSON data for PayPal 
     *
     * @return void
     */
    protected function doActionGetPayPalData()
    {
        $profile = $this->getCartProfile();
        $address = $profile->getShippingAddress();
        $client = \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance();

        $data = array(
            'flow'     => 'checkout',
            'amount'   => Converter::preparePaypalPrice($this->getCart()->getTotal()),
            'currency' => \XLite::getInstance()->getCurrency()->getCode(),
            'locale'   => 'en_US',
        );

        $modifier = $this->getCart()->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');
        $data['enableShippingAddress'] = ($modifier && $modifier->canApply());

        if (
            $address 
            && $address->getCountry() 
            && $address->getState()
        ) {
            $data['shippingAddressEditable'] = false;
            $data['shippingAddressOverride'] = ('1' == $client->getSetting('paypalShippingAddressOverride'))
                ? array(
                    'recipientName' => $address->getFirstname() . ' ' . $address->getLastname(),
                    'line1'         => $address->getStreet(),
                    'city'          => $address->getCity(),
                    'postalCode'    => $address->getZipcode(),
                    'phone'         => $address->getPhone(),
                    'countryCode'   => $address->getCountry()->getCode(),
                    'state'         => $client->getStateField($profile->getShippingAddress()),
                )
                : false;
        }

        $this->displayJSON($data);
        $this->setSuppressOutput(true);
        $this->set('silent', true);
    }

    /**
     * @return array
     */
    protected function get3dSecureParameters()
    {
        $profile = $this->getCartProfile();
        $shippingAddress = $profile->getShippingAddress();
        $billingAddress = $profile->getBillingAddress();

        $result = [
            'email'                 => $profile->getLogin(),
            'billingAddress'        => [
                'givenName'         => $billingAddress->getFirstname(),
                'surname'           => $billingAddress->getLastname(),
                'phoneNumber'       => $billingAddress->getPhone(),
                'streetAddress'     => $billingAddress->getStreet(),
                'extendedAddress'   => '',
                'locality'          => $billingAddress->getCity(),
                'region'            => \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance()->getStateField($billingAddress),
                'postalCode'        => $billingAddress->getZipcode(),
                'countryCodeAlpha2' => strtoupper($billingAddress->getCountry()->getCode()),
            ],
            'additionalInformation' => [
                'workPhoneNumber'   => '',
                'shippingGivenName' => $shippingAddress->getFirstname(),
                'shippingSurname'   => $shippingAddress->getLastname(),
                'shippingPhone'     => $shippingAddress->getPhone(),
                'shippingAddress'   => [
                    'streetAddress'     => $shippingAddress->getStreet(),
                    'extendedAddress'   => '',
                    'locality'          => $shippingAddress->getCity(),
                    'region'            => \XLite\Module\QSL\BraintreeVZ\Core\BraintreeClient::getInstance()->getStateField($shippingAddress),
                    'postalCode'        => $shippingAddress->getZipcode(),
                    'countryCodeAlpha2' => strtoupper($shippingAddress->getCountry()->getCode()),
                ],
            ],
        ];

        return $result;
    }

}
