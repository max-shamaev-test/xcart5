<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\Controller\Customer;

/**
 * Checkout 
 *
 */
class Checkout extends \XLite\Controller\Customer\Checkout implements \XLite\Base\IDecorator
{

    /**
     * Used by skin to determine if iframe should be shown
     *
     * @return void
     */
    public function isCheckoutReady()
    {
        return $this->getCart() 
            && $this->getCart()->getProfile() 
            && $this->getCart()->getProfile()->getLogin()
            && (
                $this->getCart()->getProfile()->getBillingAddress()
                || (
                    $this->getCart()->getProfile()->getShippingAddress()
                    && $this->getCart()->getProfile()->getShippingAddress()->isCompleted(\XLite\Model\Address::SHIPPING)
                )
            );
    }

    /**
     * Checkout. Recognize iframe and save that 
     *
     * @return void
     */
    protected function doActionCheckout()
    {
        if (\XLite\Core\Request::getInstance()->xpc_iframe) {

            // Enable iframe
            $this->getIframe()->enable();

            $transaction = $this->getCart()->getFirstOpenPaymentTransaction();

            $class = 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments';
            if (
                !$transaction
                || !$transaction->getPaymentMethod()
                || $class != $transaction->getPaymentMethod()->getClass()
            ) {
                // Action Checkout with "xpc_iframe" parameter was called.
                // But open transaction was not found, or a different processor is used
                // So exit.
                $this->getIframe()->setError('X-Payments transaction not found!');
                $this->getIframe()->setType(\XLite\Module\CDev\XPaymentsConnector\Core\Iframe::IFRAME_DO_NOTHING);
                $this->getIframe()->finalize();
            }

        } elseif (
            version_compare(\XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_api_version, '1.6') < 0
        ) {
            \XLite\Core\Session::getInstance()->cardSavedAtCheckout = \XLite\Core\Request::getInstance()->save_card;
        }

        parent::doActionCheckout();
    }

    /**
     * Show save card checkbox on checkout 
     *
     * @return void
     */
    public function showSaveCardBox() 
    {
        if (version_compare(\XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_api_version, '1.6') >= 0) {
            return false;
        }

        $showToUser = !$this->isAnonymous() && $this->isLogged()
            || \XLite\Core\Session::getInstance()->order_create_profile;

        $showForPayment = $this->getCart()
            && $this->getCart()->getPaymentMethod()
            && 'Y' == $this->getCart()->getPaymentMethod()->getSetting('saveCards');

        return $showToUser && $showForPayment;
    }

    /**
     * Get payment method id
     *
     * @return integer
     */
    public function getPaymentId()
    {
        return ($this->getCart() && $this->getCart()->getPaymentMethod())
            ? $this->getCart()->getPaymentMethod()->getMethodId()
            : 0;
    }

    /**
     * Get X-Payments payment methods ids
     *
     * @return array
     */
    public function getXpcPaymentIds()
    {
        $result = array();

        if ($this->getCart() && $this->getCart()->getPaymentMethods()) {
            foreach ($this->getCart()->getPaymentMethods() as $pm) {
                if ($pm->getClass() == 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments') {
                    $result[] = $pm->getMethodId();
                }
            }
        }

        return $result;
    }

    /**
     * Get X-Payments payment methods id for the saved card payment method
     *
     * @return int
     */
    public function getXpcSavedCardPaymentId()
    {
        $result = 0;

        if ($this->getCart() && $this->getCart()->getPaymentMethods()) {
            foreach ($this->getCart()->getPaymentMethods() as $pm) {
                if ($pm->getClass() == 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\SavedCard') {
                    $result = $pm->getMethodId();
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get billing address ID
     *
     * @return int
     */
    public function getXpcBillingAddressId()
    {
        $result = 0;

        if (
            $this->getCart() 
            && $this->getCart()->getProfile()
        ) {

            if ($this->getCart()->getProfile()->getBillingAddress()) {
                $result = $this->getCart()->getProfile()->getBillingAddress()->getAddressId();
            } elseif ($this->getCart()->getProfile()->getShippingAddress()) {
                $result = $this->getCart()->getProfile()->getShippingAddress()->getAddressId();
            }
        }

        return $result;
    }

    /**
     * Is iframe used or not
     *
     * @return bool 
     */
    public function isUseIframe()
    {
        return $this->getIframe()->useIframe();
    }

    /**
     * Save data of the checkout form (notes and flag to save card)
     *
     * @return void
     */
    protected function doActionSaveCheckoutFormData()
    {
        if (\XLite\Core\Request::getInstance()->notes) {
            $this->getCart()->setNotes(\XLite\Core\Request::getInstance()->notes);
        }

        if ('Y' == \XLite\Core\Request::getInstance()->save_card) {
            \XLite\Core\Session::getInstance()->cardSavedAtCheckout = 'Y';
        } else {
            \XLite\Core\Session::getInstance()->cardSavedAtCheckout = 'N';
        }

        \XLite\Core\Database::getEM()->flush();

    }

    /**
     * Clear init data from session and redirrcet back to checkout
     *
     * @return void
     */
    protected function doActionClearInitData()
    {
        \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->clearInitDataFromSession();

        $this->setHardRedirect();
        $this->setReturnURL($this->buildURL('checkout'));
        $this->doRedirect();
    }

    /**
     * Return from payment gateway
     *
     * @return void
     */
    protected function doActionReturn()
    {
        $orderId = \XLite\Core\Request::getInstance()->order_id;
        $order = \XLite\Core\Database::getRepo('XLite\Model\Order')->find($orderId);

        if ($order) {

            // Set customer notes 
            if (!empty(\XLite\Core\Request::getInstance()->notes)) {
                $order->setNotes(\XLite\Core\Request::getInstance()->notes);
            }

            // Mark card as allowed for further recharges
            // For API 1.6 this flag is set in Model\Payment\Processor\XPayments
            if (version_compare(\XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_api_version, '1.6') < 0) {

                if (
                    'Y' == \XLite\Core\Request::getInstance()->save_card
                    || 'Y' == \XLite\Core\Session::getInstance()->cardSavedAtCheckout
                ) {

                    $useForRecharges = 'Y';
                    \XLite\Core\Session::getInstance()->cardSavedAtCheckout = 'N';

                } else {

                    $useForRecharges = 'N';
                }

                foreach ($order->getPaymentTransactions() as $transaction) {

                    if ($transaction->getXpcData()) {
                        $transaction->getXpcData()->setUseForRecharges($useForRecharges);
                    }
                }

            }

            if (
                \XLite\Core\Session::getInstance()->xpc_order_create_profile
                && !($order instanceof \XLite\Model\Cart)
            ) {

                // For successfully placed orders only

                if ($order->getOrigProfile()) {

                    \XLite\Core\Auth::getInstance()->loginProfile($order->getOrigProfile());

                } elseif ($order->getProfile()) {

                    \XLite\Core\Auth::getInstance()->loginProfile($order->getProfile());
                }
            }
        }

        \XLite\Core\Database::getEM()->flush();

        parent::doActionReturn();

        if ($order) {

            $transactions = $order->getPaymentTransactions();

            $lastTransaction = $transactions->first();

            if ($lastTransaction && $lastTransaction->isXpc()) {
                $order->setPaymentStatusByTransaction($lastTransaction);

                $lastTransaction->setXpcDataCell('xpc_session_id', '');

                \XLite\Core\Database::getEM()->flush();
            }
        }
    }

    /**
     * Get class name for save card box 
     *
     * @return string 
     */
    public function getSaveCardBoxClass()
    {
        return \XLite\Core\Request::getInstance()->xpc_iframe
            ? 'save-card-box'
            : 'save-card-box-no-iframe';

    }

    /**
     * Update profile
     *
     * @return void
     */
    protected function doActionUpdateProfile()
    {
        parent::doActionUpdateProfile();

        $showSaveCardBox = $this->showSaveCardBox()
            ? 'Y'
            : 'N';

        $checkCheckoutAction = $this->checkCheckoutAction()
            ? 'Y'
            : 'N';

        \XLite\Core\Event::xpcEvent(
            array(
                'showSaveCardBox' => $showSaveCardBox,
                'checkCheckoutAction' => $checkCheckoutAction
            )
        );
    }

    /**
     * Save selected card id
     *
     * @return void
     */
    protected function doActionSaveSelectedCardId()
    {
        $selectedCardId = \XLite\Core\Request::getInstance()->selected_card_id;
        if (
            $selectedCardId 
            && $this->getCart()->getProfile()->isCardIdValid($selectedCardId)
        ) {
            \XLite\Core\Session::getInstance()->selectedCardId = $selectedCardId;
        }
        // To avoid extra AJAX parsing of response
        $this->silent = true;
    }

    /**
     * Get selected card id
     *
     * @return int
     */
    public function getSelectedCardId()
    {
        $selectedCardId = \XLite\Core\Session::getInstance()->selectedCardId;
        if (
            $selectedCardId 
            && $this->getCart()->getProfile()->isCardIdValid($selectedCardId)
        ) {
            return $selectedCardId;
        }

        return null;

    }

    /**
     * Check is card selected
     *
     * @return bool
     */
    public function isCardSelected($card)
    {
        $selectedCardId = $this->getSelectedCardId();
        if ($selectedCardId) {
            return ($selectedCardId == $card['card_id']);
        } else {
            return $card['is_default'];
        }
    }
    
    /**
     * Set card billing address
     *
     * @return void
     */
    protected function doActionSetCardBillingAddress()
    {
        $addressId = \XLite\Core\Request::getInstance()->address_id;

        // Get list of Address IDs associated with profile
        $profileAddressIds = array_keys(
            \XLite\Module\CDev\XPaymentsConnector\Core\ZeroAuth::getInstance()->getAddressList($this->getCart()->getProfile())
        );        

        if (!in_array($addressId, $profileAddressIds)) {

            // This address is not associated with the customer's profile
            \XLite\Core\TopMessage::addError('Address not found');

        } else {

            $addresses = $this->getCart()->getProfile()->getAddresses();

            foreach ($addresses as $address) {
                if ($addressId == $address->getAddressId()) {
                    $address->setIsBilling(true);
                } else {
                    $address->setIsBilling(false);
                }
            }

            $shippingAddressId = $this->getCart()->getProfile()->getShippingAddress()->getAddressId();

            $sameAddress = ($addressId == $shippingAddressId);

            \XLite\Core\Session::getInstance()->same_address = $sameAddress;

            \XLite\Core\Event::selectCartAddress(
                array(
                    'type'      => \XLite\Model\Address::BILLING,
                    'addressId' => $addressId,
                    'same'      => $sameAddress,
                )
            );

            \XLite\Core\Database::getEM()->flush();

            $this->updateCart();

            $this->silenceClose = true; 
        }
    }    
}
