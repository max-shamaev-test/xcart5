<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Model\Payment\Processor;

use XLite\Core\TopMessage;
use XLite\Model\Payment\BackendTransaction;
use XLite\Model\Payment\Transaction;
use XPaymentsCloud\ApiException;
use XPaymentsCloud\Model\Payment as XpPayment;
use XLite\Core\Session;
use XLite\Module\XPay\XPaymentsCloud\Model\Payment\XpaymentsFraudCheckData as FraudCheckData;
use \XLite\Model\Order;

class XPaymentsCloud extends \XLite\Model\Payment\Base\CreditCard
{
    /***
     * Simply redirect to 3-D Secure page instead of embedding it
     */
    const SIMPLE_3D_SECURE_MODE = false;

    /*
     * Allowed secondary actions status values for transactions
     */
    const ACTION_ALLOWED = 'Yes';
    const ACTION_PART = 'Yes, partial';
    const ACTION_MULTI = 'Yes, multiple';
    const ACTION_NOTALLOWED = 'No';

    /**
     * Get allowed backend transactions
     *
     * @return string[] Status code
     */
    public function getAllowedTransactions()
    {
        return array(
            BackendTransaction::TRAN_TYPE_CAPTURE,
            BackendTransaction::TRAN_TYPE_CAPTURE_PART,
            BackendTransaction::TRAN_TYPE_VOID,
            BackendTransaction::TRAN_TYPE_REFUND,
            BackendTransaction::TRAN_TYPE_REFUND_PART,
            BackendTransaction::TRAN_TYPE_REFUND_MULTI,
            BackendTransaction::TRAN_TYPE_ACCEPT,
            BackendTransaction::TRAN_TYPE_DECLINE,
        );
    }

    /**
     * Do initial payment
     *
     * @return string Status code
     */
    protected function doInitialPayment()
    {
        $api = $this->initClient();

        $token = \XLite\Core\Request::getInstance()->xpaymentsToken;

        try {
            $response = $api->doPay(
                $token,
                $this->getTransactionId(),
                $this->getXpaymentsCustomerId(),
                $this->prepareCart(),
                $this->getReturnURL(null, true),
                $this->getCallbackURL(null, true)
            );

            $payment = $response->getPayment();
            $status = $payment->status;
            $note = $payment->message;

            $this->processXpaymentsFraudCheckData($this->transaction, $payment);

            if (!is_null($response->redirectUrl)) {
                // Should redirect to continue payment
                $this->transaction->setXpaymentsId($payment->xpid);

                $url = $response->redirectUrl;
                if (!\XLite\Core\Converter::isURL($url)) {
                    throw new \XPaymentsCloud\ApiException('Invalid 3-D Secure URL');
                }

                if (static::SIMPLE_3D_SECURE_MODE) {
                    $result = static::PROLONGATION;
                    $this->redirectToPay($url);
                } else {
                    $result = static::SEPARATE;

                    Session::getInstance()->xpaymentsData = [
                        'redirectUrl' => $url,
                    ];
                }

            } else {
                $result = $this->processPaymentFinish($this->transaction, $payment);
                if (static::FAILED == $result) {
                    TopMessage::addError($note);
                }

            }

        } catch (\XPaymentsCloud\ApiException $exception) {
            $result = static::FAILED;
            $note = $exception->getMessage();
            $this->transaction->setDataCell('xpaymentsMessage', $note, 'Message');
            $this->log('Error: ' . $note);
            $message = $exception->getPublicMessage();
            if (!$message) {
                $message = 'Failed to process the payment!';
            }
            \XLite\Core\TopMessage::addError($message);
        }

        $this->transaction->setNote($note);

        return $result;
   }

    /**
     * @param \XLite\Model\Payment\Transaction $transaction
     *
     * @return boolean
     */
    protected function isCaptureTransactionAllowed(Transaction $transaction)
    {
        return $transaction->isCaptureTransactionAllowed()
            && (static::ACTION_NOTALLOWED != $transaction->getDetail('xpaymentsCapture'))
            && !$this->isManualReviewFraudStatus($transaction)
            && !$this->isPendingFraudStatus($transaction);
    }

    /**
     * @param \XLite\Model\Payment\Transaction $transaction
     *
     * @return boolean
     */
    protected function isRefundTransactionAllowed(Transaction $transaction)
    {
        return $transaction->isRefundTransactionAllowed()
            && (static::ACTION_NOTALLOWED != $transaction->getDetail('xpaymentsRefund'))
            && !$this->isManualReviewFraudStatus($transaction)
            && !$this->isPendingFraudStatus($transaction);
    }

    /**
     * @param \XLite\Model\Payment\Transaction $transaction
     *
     * @return boolean
     */
    protected function isVoidTransactionAllowed(Transaction $transaction)
    {
        return $transaction->isVoidTransactionAllowed()
            && (static::ACTION_NOTALLOWED != $transaction->getDetail('xpaymentsVoid'))
            && !$this->isManualReviewFraudStatus($transaction)
            && !$this->isPendingFraudStatus($transaction);
    }

    /**
     * Check capture (partially) operation availability
     *
     * @param \XLite\Model\Payment\Transaction $transaction Backend transaction
     *
     * @return boolean
     */
    protected function isCapturePartTransactionAllowed(\XLite\Model\Payment\Transaction $transaction)
    {
        return $transaction->isCaptureTransactionAllowed()
            && in_array(
                $transaction->getDetail('xpaymentsCapture'),
                [
                    static::ACTION_PART,
                    static::ACTION_MULTI
                ]
            )
            && !$this->isManualReviewFraudStatus($transaction)
            && !$this->isPendingFraudStatus($transaction);
    }

    /**
     * Check capture (multiple) operation availability
     *
     * @param \XLite\Model\Payment\Transaction $transaction Backend transaction
     *
     * @return boolean
     */
    protected function isCaptureMultiTransactionAllowed(\XLite\Model\Payment\Transaction $transaction)
    {
        return
            false && // Currently not supported
            $transaction->isCaptureTransactionAllowed()
            && (static::ACTION_MULTI == $transaction->getDetail('xpaymentsCapture'))
            && !$this->isManualReviewFraudStatus($transaction)
            && !$this->isPendingFraudStatus($transaction);
    }

    /**
     * Check refund (partially) operation availability
     *
     * @param \XLite\Model\Payment\Transaction $transaction Backend transaction
     *
     * @return boolean
     */
    protected function isRefundPartTransactionAllowed(\XLite\Model\Payment\Transaction $transaction)
    {
        return $transaction->isRefundPartTransactionAllowed()
            && in_array(
                $transaction->getDetail('xpaymentsRefund'),
                [
                    static::ACTION_PART,
                    static::ACTION_MULTI
                ]
            )
            && !$this->isManualReviewFraudStatus($transaction)
            && !$this->isPendingFraudStatus($transaction);
    }

    /**
     * Check refund (multiple) operation availability
     *
     * @param \XLite\Model\Payment\Transaction $transaction Backend transaction
     *
     * @return boolean
     */
    protected function isRefundMultiTransactionAllowed(\XLite\Model\Payment\Transaction $transaction)
    {
        return $transaction->isRefundMultiTransactionAllowed()
            && (static::ACTION_MULTI == $transaction->getDetail('xpaymentsRefund'))
            && !$this->isManualReviewFraudStatus($transaction)
            && !$this->isPendingFraudStatus($transaction);
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    protected function isPendingFraudStatus(Transaction $transaction)
    {
        $result = false;
        $fraudData = $transaction->getXpaymentsFraudCheckData();
        if ($fraudData) {
            foreach ($fraudData as $fraudDataItem) {
                if ($fraudDataItem->isPending()) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    protected function isManualReviewFraudStatus(Transaction $transaction)
    {
        $result = false;
        $fraudData = $transaction->getXpaymentsFraudCheckData();
        if ($fraudData) {
            foreach ($fraudData as $fraudDataItem) {
                if ($fraudDataItem->isManualReview()) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    protected function isAcceptTransactionAllowed(Transaction $transaction)
    {
        return $this->isManualReviewFraudStatus($transaction);
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    protected function isDeclineTransactionAllowed(Transaction $transaction)
    {
        return $this->isManualReviewFraudStatus($transaction);
    }

    /**
     * @param \XLite\Model\Payment\BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doCapture(BackendTransaction $transaction)
    {
        return $this->doSecondary('capture', $transaction);
    }

    /**
     * @param BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doCapturePart(BackendTransaction $transaction)
    {
        return $this->doCapture($transaction);
    }

    /**
     * @param BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doCaptureMulti(BackendTransaction $transaction)
    {
        return $this->doCapture($transaction);
    }

    /**
     * @param \XLite\Model\Payment\BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doRefund(BackendTransaction $transaction)
    {
        return $this->doSecondary('refund', $transaction);
    }

    /**
     * @param BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doRefundPart(BackendTransaction $transaction)
    {
        return $this->doRefund($transaction);
    }

    /**
     * @param BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doRefundMulti(BackendTransaction $transaction)
    {
        return $this->doRefund($transaction);
    }

    /**
     * @param \XLite\Model\Payment\BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doVoid(BackendTransaction $transaction)
    {
        $result = $this->doSecondary('void', $transaction);
        
        if ($result) {
            $transaction->getPaymentTransaction()->setStatus(Transaction::STATUS_VOID);
        }

        return $result;
    }

    /**
     * @param BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doAccept(BackendTransaction $transaction)
    {
        return $this->doSecondary('accept', $transaction);
    }

    /**
     * @param BackendTransaction $transaction Transaction
     *
     * @return boolean
     */
    protected function doDecline(BackendTransaction $transaction)
    {
        return $this->doSecondary('decline', $transaction);
    }

    /**
     * Auxiliary function for secondary actions execution
     *
     * @param $action
     * @param BackendTransaction $transaction
     *
     * @return bool
     */
    private function doSecondary($action, BackendTransaction $transaction)
    {
        $result = false;

        $paymentTransaction = $transaction->getPaymentTransaction();

        $api = $this->initClient();

        try {
            $methodName = 'do' . ucfirst($action);
            $response = $api->$methodName(
                $paymentTransaction->getXpaymentsId(),
                $transaction->getValue()
            );

            $status = $response->result;

            if ($status) {
                $payment = $response->getPayment();
                $result = true;

                if (BackendTransaction::TRAN_TYPE_DECLINE == $action) {
                    $this->registerBackendTransaction($paymentTransaction, $payment);
                }

                $transaction->setStatus(BackendTransaction::STATUS_SUCCESS);
                $this->processXpaymentsFraudCheckData($paymentTransaction, $payment);
                \XLite\Core\TopMessage::addInfo($payment->message);
            } else {
                throw new ApiException($response->message ?: 'Operation failed');
            }

        } catch (ApiException $exception) {
            $result = false;
            $note = $exception->getMessage();
            $this->log('Error: ' . $note);
            // Show error because it is visible to admin only
            \XLite\Core\TopMessage::addError($note);
        }

        return $result;
    }

    /**
     * Get name for the transaction data cell
     *
     * @param string $title
     * @param string $prefix
     *
     * @return string
     */
    private function getTransactionDataCellName($title, $prefix = '')
    {
        return $prefix . \XLite\Core\Converter::convertToCamelCase(
            preg_replace('/[^a-z0-9_-]+/i', '_', $title)
        );
    }

    /**
     * Sets all required transaction data cells for further operations
     *
     * @param \XLite\Model\Payment\Transaction $transaction
     * @param \XPaymentsCloud\Model\Payment $payment
     */
    private function setTransactionDataCells(Transaction $transaction, XpPayment $payment)
    {
        $transaction->setXpaymentsId($payment->xpid);
        $transaction->setDataCell('xpaymentsMessage', $payment->message, 'Message');

        $actions = [
            'capture' => 'Capture',
            'void' => 'Void',
            'refund' => 'Refund',
        ];

        foreach ($actions as $action => $cellName) {
            $can = ($payment->isTransactionSupported($action)) ? static::ACTION_ALLOWED : static::ACTION_NOTALLOWED;
            if (static::ACTION_ALLOWED == $can) {
                if ($payment->isTransactionSupported($action . 'Multi')) {
                    $can = static::ACTION_MULTI;
                } elseif ($payment->isTransactionSupported($action . 'Part')) {
                    $can = static::ACTION_PART;
                }
            }
            $transaction->setDataCell('xpayments' . $cellName, $can, $cellName);

        }

        if (is_object($payment->details)) {

            // Set payment details i.e. something that returned from the gateway

            $details = get_object_vars($payment->details);

            foreach ($details as $title => $value) {
                if (!empty($value) && !preg_match('/(\[Kount\]|\[NoFraud\]|\[Signifyd\])/i', $title)) {
                    $name = $this->getTransactionDataCellName($title, 'xpaymentsDetails.');
                    $transaction->setDataCell($name, $value, $title);
                }
            }
        }

        if (is_object($payment->verification)) {

            // Set verification (AVS and CVV) 

            if (!empty($payment->verification->avsRaw)) {
                $transaction->setDataCell('xpaymentsAvsResult', $value, 'AVS Check Result');
            }

            if (!empty($payment->verification->cvvRaw)) {
                $transaction->setDataCell('xpaymentsCvvResult', $value, 'CVV Check Result');
            }
        }

        if (
            is_object($payment->card)
            && !empty($payment->card->last4)
        ) {

            // Set masked card details

            if (empty($payment->card->first6)) {
                $first6 = '******';
            } else {
                $first6 = $payment->card->first6;
            }

            $transaction->setDataCell(
                'xpaymentsCardNumber',
                sprintf('%s******%s', $first6, $payment->card->last4),
                'Card number',
                'C'
            );

            if (
                !empty($payment->card->expireMonth)
                && !empty($payment->card->expireYear)
            ) {

                $transaction->setDataCell(
                    'xpaymentsCardExpirationDate',
                    sprintf('%s/%s', $payment->card->expireMonth, $payment->card->expireYear),
                    'Expiration date',
                    'C'
                );
            }

            if (!empty($payment->card->type)) {
                $transaction->setDataCell(
                    'xpaymentsCardType',
                    $payment->card->type,
                    'Card type',
                    'C'
                );
            }

            if (!empty($payment->card->cardholderName)) {
                $transaction->setDataCell(
                    'xpaymentsCardholder',
                    $payment->card->cardholderName,
                    'Cardholder name',
                    'C'
                );
            }
        }
    }

    /**
     * Set initial payment transaction status by response status
     *
     * @param \XLite\Model\Payment\Transaction $transaction Transaction to update
     * @param integer $responseStatus Transaction status from X-Payments
     *
     * @return void
     */
    protected function setTransactionTypeByStatus(Transaction $transaction, $responseStatus)
    {
        // Initial transaction type is not known currently before payment, try to guess it from X-P transaction status
        if (XpPayment::AUTH == $responseStatus) {
            $transaction->setType(\XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH);
        } elseif (XpPayment::CHARGED == $responseStatus) {
            $transaction->setType(\XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE);
        }
    }

    /*
     * Load X-Payments Cloud SDK
     */
    private function loadApi()
    {
        require_once LC_DIR_MODULES . 'XPay' . LC_DS . 'XPaymentsCloud' . LC_DS . 'lib' . LC_DS . 'XPaymentsCloud' . LC_DS . 'Client.php';
    }

    /*
     * Init SDK Client
     *
     * @return \XPaymentsCloud\Client
     */
    protected function initClient()
    {
        $this->loadApi();

        return new \XPaymentsCloud\Client(
            $this->getSetting('account'),
            $this->getSetting('api_key'),
            $this->getSetting('secret_key')
        );
    }

    /**
     * Get payment method input template
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getInputTemplate()
    {
        return 'modules/XPay/XPaymentsCloud/widget.twig';
    }

    /**
     * @return string
     */
    public function getSettingsWidget()
    {
        return '\XLite\Module\XPay\XPaymentsCloud\View\ConnectWidget';
    }

    /**
     * @return bool
     */
    public function useDefaultSettingsFormButton()
    {
        return false;
    }

    /**
     * Payment is configured when required keys set and HTTPS enabled
     *
     * @param \XLite\Model\Payment\Method $method
     *
     * @return bool
     */
    public function isConfigured(\XLite\Model\Payment\Method $method)
    {
        $httpsEnabled = \XLite\Core\Config::getInstance()->Security->admin_security
            && \XLite\Core\Config::getInstance()->Security->customer_security;

        return parent::isConfigured($method)
            && $method->getSetting('account')
            && $method->getSetting('api_key')
            && $method->getSetting('secret_key')
            && $method->getSetting('widget_key')
            && $httpsEnabled;
    }

    /**
     * Process callback
     *
     * @param Transaction $transaction Callback-owner transaction
     *
     * @return void
     *
     * @throws \XLite\Core\Exception\PaymentProcessing\CallbackRequestError
     */
    public function processCallback(Transaction $transaction)
    {
        parent::processCallback($transaction);

        if ($this->transaction->isXpayments()) {
            $api = $this->initClient();

            try {
                $response = $api->parseCallback();
            } catch (\XPaymentsCloud\ApiException $exception) {
                throw new \XLite\Core\Exception\PaymentProcessing\CallbackRequestError($exception->getMessage());
            }

            $payment = $response->getPayment();

            $this->processXpaymentsFraudCheckData($this->transaction, $payment);

            if (0 !== strcmp($transaction->getXpaymentsId(), $payment->xpid)) {
                // This is a rebill
                $parentTransaction = $transaction;
                $transaction = \XLite\Core\Database::getRepo('XLite\Model\Payment\Transaction')
                                        ->findOneByCell('xpaymentsPaymentId', $payment->xpid);
                if (!$transaction) {
                    $transaction = $this->createChildTransaction($parentTransaction, $payment);
                }
            }

            $this->registerBackendTransaction($transaction, $payment);

        } else {
            throw new \XLite\Core\Exception\PaymentProcessing\CallbackRequestError('Couldn\'t find an X-Payments Cloud payment for callback!');
        }
    }

    /**
     * Process return from 3-D Secure form and complete payment
     *
     * @param Transaction $transaction
     */
    public function processReturn(Transaction $transaction)
    {
        parent::processReturn($transaction);

        if (!empty(Session::getInstance()->xpaymentsData)) {
            unset(Session::getInstance()->xpaymentsData);
        }

        if ($this->transaction->isXpayments()) {

            $api = $this->initClient();

            try {
                $response = $api->doContinue(
                    $this->transaction->getXpaymentsId()
                );

                $payment = $response->getPayment();
                $note = $payment->message;

                $this->processXpaymentsFraudCheckData($this->transaction, $payment);

                $result = $this->processPaymentFinish($transaction, $payment);


            } catch (\XPaymentsCloud\ApiException $exception) {
                $result = static::FAILED;
                $note = $exception->getMessage();
                $this->transaction->setDataCell('xpaymentsMessage', $note, 'Message');
                // Add note to log, but exact error shouldn't be shown to customer
                $this->log('Error: ' . $note);
                TopMessage::addError('Failed to process the payment!');
            }

            $this->transaction->setNote($note);
            $this->transaction->setStatus($result);
        } else {
            // Invalid non-XP transaction
            TopMessage::addError('Transaction was lost!');
        }
    }

    /**
     * Finalize initial transaction
     *
     * @param Transaction $transaction
     * @param \XPaymentsCloud\Model\Payment $payment
     *
     * @return string
     */
    private function processPaymentFinish(Transaction $transaction, XpPayment $payment)
    {
        $this->setTransactionDataCells($transaction, $payment);

        if ($payment->initialTransactionId) {
            $transaction->setPublicId($payment->initialTransactionId . ' (' . $transaction->getPublicId() . ')');
        }

        if ($payment->customerId) {
            $transaction->getOrigProfile()->setXpaymentsCustomerId($payment->customerId);
        }

        $status = $payment->status;

        if (
            XpPayment::AUTH == $status
            || XpPayment::CHARGED == $status
        ) {
            $result = static::COMPLETED;
            $this->setTransactionTypeByStatus($transaction, $status);

        } elseif (
            XpPayment::DECLINED == $status
        ) {
            $result = static::FAILED;

        } else {
            $result = static::PENDING;
        }

        return $result;
    }

    /**
     * Prepare X-Payments Customer Id
     *
     * @return string
     */
    public function getXpaymentsCustomerId()
    {
        $profile = $this->transaction->getOrigProfile();
        return $profile->getXpaymentsCustomerId();
    }

    /**
     * Prepare shopping cart data
     *
     * @return array
     */
    public function prepareCart()
    {
        $cart = $this->transaction->getOrder();

        $profile = $cart->getProfile();

        if ($cart->getOrderNumber()) {

            $description = 'Order #' . $cart->getOrderNumber();

        } else {

            $description = $this->getInvoiceDescription();
        }

        // Try modern serialized emails or fallback to plain string
        $emails = @unserialize(\XLite\Core\Config::getInstance()->Company->orders_department);
        $merchantEmail = (is_array($emails) && !empty($emails))
            ? array_shift($emails)
            : \XLite\Core\Config::getInstance()->Company->orders_department;

        $result = array(
            'login'                => $profile->getLogin() . ' (User ID #' . $profile->getProfileId() . ')',
            'items'                => array(),
            'currency'             => \XLite::getInstance()->getCurrency()->getCode(),
            'shippingCost'         => 0.00,
            'taxCost'              => 0.00,
            'discount'             => 0.00,
            'totalCost'            => 0.00,
            'description'          => $description,
            'merchantEmail'        => $merchantEmail,

        );

        if (
            $profile->getBillingAddress()
            && $profile->getShippingAddress()
        ) {

            $result['billingAddress'] = $this->prepareAddress($profile);
            $result['shippingAddress'] = $this->prepareAddress($profile, 'shipping');

        } elseif (
            $profile->getBillingAddress()
            && !$profile->getShippingAddress()
        ) {

            $result['billingAddress'] = $result['shippingAddress'] = $this->prepareAddress($profile);

        } else {

            $result['billingAddress'] = $result['shippingAddress'] = $this->prepareAddress($profile, 'shipping');
        }

        // Set items
        if ($cart->getItems()) {

            foreach ($cart->getItems() as $item) {

                $itemElement = array(
                    'sku'      => strval($item->getSku() ? $item->getSku() : $item->getName()),
                    'name'     => strval($item->getName() ? $item->getName() : $item->getSku()),
                    'price'    => $this->roundCurrency($item->getPrice()),
                    'quantity' => $item->getAmount(),
                );

                if (!$itemElement['sku']) {
                    $itemElement['sku'] = 'N/A';
                }

                if (!$itemElement['name']) {
                    $itemElement['name'] = 'N/A';
                }

                $result['items'][] = $itemElement;
            }
        }

        // Set costs
        $result['shippingCost'] = $this->roundCurrency(
            $cart->getSurchargesSubtotal(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, false)
        );
        $result['taxCost'] = $this->roundCurrency(
            $cart->getSurchargesSubtotal(\XLite\Model\Base\Surcharge::TYPE_TAX, false)
        );
        $result['totalCost'] = $this->roundCurrency($cart->getTotal());
        $result['discount'] = $this->roundCurrency(
            abs($cart->getSurchargesSubtotal(\XLite\Model\Base\Surcharge::TYPE_DISCOUNT, false))
        );

        return $result;
    }

    /**
     * Prepare address data
     *
     * @param \XLite\Model\Profile $profile Customer's profile
     * @param $type Address type, billing or shipping
     *
     * @return array
     */
    protected function prepareAddress(\XLite\Model\Profile $profile, $type = 'billing')
    {
        $result = array();

        $addressFields = array(
            'firstname' => 'N/A',
            'lastname'  => '',
            'address'   => 'N/A',
            'city'      => 'N/A',
            'state'     => 'N/A',
            'country'   => 'XX', // WA fix for MySQL 5.7 with strict mode
            'zipcode'   => 'N/A',
            'phone'     => '',
            'fax'       => '',
            'company'   => '',
        );

        $repo = \XLite\Core\Database::getRepo('\XLite\Model\AddressField');

        $type = $type . 'Address';

        foreach ($addressFields as $field => $defValue) {

            $method = 'address' == $field ? 'street' : $field;
            $address = $profile->$type;

            if (
                $address
                && ($repo->findOneBy(array('serviceName' => $method)) || method_exists($address, 'get' . $method))
                && $address->$method
            ) {
                $result[$field] = is_object($profile->$type->$method)
                    ? $profile->$type->$method->getCode()
                    : $profile->$type->$method;
            }

            if (empty($result[$field])) {
                $result[$field] = $defValue;
            }
        }

        $result['email'] = $profile->getLogin();

        return $result;
    }

    /**
     * Round currency
     *
     * @param float $data Data
     *
     * @return float
     */
    protected function roundCurrency($data)
    {
        return sprintf('%01.2f', round($data, 2));
    }

    /**
     * Create transaction by parent one and data passed from X-Payments Cloud
     *
     * @param \XLite\Model\Payment\Transaction $parentTransaction Parent transaction
     * @param \XPaymentsCloud\Model\Payment $payment Payment from X-Payments
     *
     * @return \XLite\Model\Payment\Transaction
     *
     */
    protected function createChildTransaction($parentTransaction, XpPayment $payment)
    {
        $parentOrder = $parentTransaction->getOrder();

        $cart = $this->createCart(
            $parentOrder->getOrigProfile(),
            $parentTransaction->getPaymentMethod(),
            $payment->amount,
            $payment->description ?: 'Extra charges',
            're-bill'
        );

        $transaction = $cart->getFirstOpenPaymentTransaction();

        if ($transaction) {
            $this->processPaymentFinish($transaction, $payment);
        }

        if (XpPayment::INITIALIZED != $payment->status) {
            $transaction->setStatus(Transaction::STATUS_SUCCESS);
            $cart->setPaymentStatus(\XLite\Model\Order\Status\Payment::STATUS_PAID);
        }
        $cart->processSucceed();

        return $transaction;
    }

    /**
     * Create a cart with non existing item with required total
     *
     * @param \XLite\Model\Profile $profile Customer's profile for whom the cart is created for
     * @param \XLite\Model\Payment\Method $paymentMethod Payment methood
     * @param float $total Cart total
     * @param string $itemName Name of the fake item
     * @param string $itemSku SKU of the fake item
     *
     * @return \XLite\Model\Cart
     *
     */
    public function createCart(\XLite\Model\Profile $profile, \XLite\Model\Payment\Method $paymentMethod, $total, $itemName, $itemSku)
    {
        $cart = new \XLite\Model\Cart;

        $cart->setTotal($total);
        $cart->setSubtotal($total);
        $cart->setCurrency(\XLite::getInstance()->getCurrency());
        $cart->setDate(time());
        \XLite\Core\Database::getEM()->persist($cart);
        \XLite\Core\Database::getEM()->flush();

        $cart->setOrderNumber(\XLite\Core\Database::getRepo('XLite\Model\Order')->findNextOrderNumber());
        $cart->setProfileCopy($profile);
        $cart->setLastShippingId(null);
        $cart->setPaymentMethod($paymentMethod, $total);

        $item = new \XLite\Model\OrderItem;
        $item->setName($itemName);
        $item->setSku($itemSku);
        $item->setPrice($total);
        $item->setAmount(1);
        $item->setTotal($total);
        $item->setSubtotal($total);
        $item->setDiscountedSubtotal($total);
        $item->setXpaymentsEmulated(true);

        \XLite\Core\Database::getEM()->persist($item);

        $cart->addItem($item);

        \XLite\Core\Database::getEM()->flush();

        return $cart;
    }

    /**
     * Register backend transaction
     *
     * @param \XLite\Model\Payment\Transaction $transaction Callback-owner transaction
     * @param \XPaymentsCloud\Model\Payment $payment
     *
     * @return void
     */
    protected function registerBackendTransaction(Transaction $transaction, XpPayment $payment)
    {
        $type = null;
        $value = null;

        switch ($payment->status) {
            case XpPayment::INITIALIZED:
                $type = BackendTransaction::TRAN_TYPE_SALE;
                break;

            case XpPayment::AUTH:
                $type = BackendTransaction::TRAN_TYPE_AUTH;
                break;

            case XpPayment::DECLINED:
                if (0 == $payment->authorized->amount && 0 == $payment->charged->amount) {
                    $type = BackendTransaction::TRAN_TYPE_DECLINE;
                } else {
                    $type = BackendTransaction::TRAN_TYPE_VOID;
                }
                break;

            case XpPayment::CHARGED:
                if ($payment->amount == $payment->charged->amount) {
                    $type = BackendTransaction::TRAN_TYPE_CAPTURE;
                    $value = $this->getActualAmount('captured', $transaction, $payment->amount);
                } else {
                    $type = BackendTransaction::TRAN_TYPE_CAPTURE_PART;
                    $value = $this->getActualAmount('captured', $transaction, $payment->amount);
                }
                break;

            case XpPayment::REFUNDED:
                $type = BackendTransaction::TRAN_TYPE_REFUND;
                $value = $this->getActualAmount('refunded', $transaction, $payment->amount);
                break;

            case XpPayment::PART_REFUNDED:
                $type = BackendTransaction::TRAN_TYPE_REFUND_PART;
                $value = $this->getActualAmount('refunded', $transaction, $payment->amount);
                break;

            default:

        }

        if ($type) {
            $backendTransaction = $transaction->createBackendTransaction($type);
            if (XpPayment::INITIALIZED != $payment->status) {
                $backendTransaction->setStatus(BackendTransaction::STATUS_SUCCESS);
            }
            if (0.01 <= $value) {
                $backendTransaction->setValue($value);
            }
            $backendTransaction->setDataCell('xpaymentsMessage', $payment->message);
            $backendTransaction->registerTransactionInOrderHistory('callback');
        }
    }

    /**
     * Get transaction refunded amount
     *
     * @param string $action 'refunded' or 'captured'
     * @param \XLite\Model\Payment\Transaction $transaction Callback-owner transaction
     * @param float $baseAmount Amount received in callback
     *
     * @return float
     */
    protected function getActualAmount($action, Transaction $transaction, $baseAmount)
    {
        $amount = 0;
        $btTypes = [];
        if ('refunded' == $action) {
            $btTypes = [
                BackendTransaction::TRAN_TYPE_REFUND,
                BackendTransaction::TRAN_TYPE_REFUND_PART,
                BackendTransaction::TRAN_TYPE_REFUND_MULTI,
            ];
        } elseif ('captured' == $action) {
            $btTypes = [
                BackendTransaction::TRAN_TYPE_CAPTURE,
                BackendTransaction::TRAN_TYPE_CAPTURE_PART,
                BackendTransaction::TRAN_TYPE_CAPTURE_MULTI,
            ];
        }

        foreach ($transaction->getBackendTransactions() as $bt) {
            if ($bt->isCompleted() && in_array($bt->getType(), $btTypes)) {
                $amount += $bt->getValue();
            }
        }

        $amount = $baseAmount - $amount;

        return max(0, $amount);
    }

    /**
     * Get admin URL of X-Payments
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getXpaymentsAdminUrl()
    {
        return $this->initClient()->getAdminUrl();
    }

    /**
     * Get payment method admin zone icon URL
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getAdminIconURL(\XLite\Model\Payment\Method $method)
    {
        return true;
    }

    /**
     * Wrapper for logger
     *
     * @param $message
     */
    private function log($message)
    {
        \XLite\Logger::getInstance()->logCustom('XPaymentsCloud', $message);
    }

    /**
     * Redirect customer to X-Payments (for 3-d Secure)
     *
     * @param string $url Url
     *
     * @return void
     */
    protected function redirectToPay($url)
    {
        $url = str_replace('\'', '', \Includes\Utils\Converter::removeCRLF($url));
        $page = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body onload="self.location = '$url';">
</body>
</html>
HTML;

        echo $page;
    }

    /**
     * Process Fraud Check data
     *
     * @param Transaction $transaction
     * @param XpPayment $payment
     *
     * @return void
     */
    protected function processXpaymentsFraudCheckData(Transaction $transaction, XpPayment $payment)
    {
        if (!$payment->fraudCheck) {
            return;
        }

        $oldFraudCheckData = $transaction->getXpaymentsFraudCheckData();
        if ($oldFraudCheckData) {
            foreach ($transaction->getXpaymentsFraudCheckData() as $fraudCheckData) {
                \XLite\Core\Database::getEM()->remove($fraudCheckData);
            }
        }

        // Maximum fraud result within several services (if there are more than one)
        $maxFraudResult = FraudCheckData::RESULT_UNKNOWN;

        // Code of the service which got "most fraud" result
        $maxFraudResultCode = '';

        // Flag to check if any errors which prevented fraud check occurred
        $errorsFound = false;

        foreach ($payment->fraudCheck as $service) {

            // Ignore "noname" services. This must be filled in on the X-Payments Cloud side
            if (!$service['code'] || !$service['service']) {
                continue;
            }

            if (!$maxFraudResultCode) {
                // Use first the code, so that something is specified
                $maxFraudResultCode = $service['code'];
            }

            $fraudCheckData = new FraudCheckData;
            $fraudCheckData->setTransaction($transaction);

            $transaction->addXpaymentsFraudCheckData($fraudCheckData);

            $fraudCheckData->setCode($service['code']);
            $fraudCheckData->setService($service['service']);

            $module = $service['module'];

            $fraudCheckData->setModule($module);

            if (!empty($service['result'])) {
                $fraudCheckData->setResult($service['result']);

                if (intval($service['result']) > $maxFraudResult) {
                    $maxFraudResult = intval($service['result']);
                    $maxFraudResultCode = $service['code'];
                }
            }

            if (!empty($service['status'])) {
                $fraudCheckData->setStatus($service['status']);
            }

            if (!empty($service['score'])) {
                $fraudCheckData->setScore($service['score']);
            }

            if (!empty($service['transactionId'])) {
                $fraudCheckData->setServiceTransactionId($service['transactionId']);
            }

            if (!empty($service['url'])) {
                $fraudCheckData->setUrl($service['url']);
            }

            if (!empty($service['message'])) {
                $fraudCheckData->setMessage($service['message']);

                if (FraudCheckData::RESULT_UNKNOWN == $service['result']) {
                    // Unknown result with message should be shown as error
                    $errorsFound = true;
                }
            }

            if (!empty($service['errors'])) {
                $errors = implode("\n", $service['errors']);
                $fraudCheckData->setErrors($errors);
                $errorsFound = true;
            }

            if (!empty($service['rules'])) {
                $rules = implode("\n", $service['rules']);
                $fraudCheckData->setRules($rules);
            }

            if (!empty($service['warnings'])) {
                $warnings = implode("\n", $service['warnings']);
                $fraudCheckData->setWarnings($warnings);
            }
        }

        // Convert maximum fraud result to the order's fraud status
        $status = Order::FRAUD_STATUS_UNKNOWN;
        switch ($maxFraudResult) {

            case FraudCheckData::RESULT_UNKNOWN:
                if ($errorsFound) {
                    $status = Order::FRAUD_STATUS_ERROR;
                } else {
                    $status = Order::FRAUD_STATUS_UNKNOWN;
                }
                break;

            case FraudCheckData::RESULT_ACCEPTED:
                $status = Order::FRAUD_STATUS_CLEAN;
                break;

            case FraudCheckData::RESULT_MANUAL:
            case FraudCheckData::RESULT_PENDING:
                $status = Order::FRAUD_STATUS_REVIEW;
                break;

            case FraudCheckData::RESULT_FAIL:
                $status = Order::FRAUD_STATUS_FRAUD;
                break;
        }

        $transaction->getOrder()
            ->setXpaymentsFraudStatus($status)
            ->setXpaymentsFraudType($maxFraudResultCode)
            ->setXpaymentsFraudCheckTransactionId($transaction->getTransactionId());
    }

}
