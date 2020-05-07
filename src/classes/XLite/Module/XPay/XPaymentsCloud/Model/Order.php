<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Model;

use XLite\Model\Payment\BackendTransaction;
use XLite\Module\XPay\XPaymentsCloud\Model\Payment\XpaymentsFraudCheckData as FraudCheckData;
use XLite\Model\Payment\Transaction;
use XLite\Model\Order\Status\Payment as PaymentStatus;

/**
 * X-Payments Specific order fields
 *
 */
class Order extends \XLite\Model\Order implements \XLite\Base\IDecorator
{
    /**
     * Fraud statuses
     */
    const FRAUD_STATUS_CLEAN    = 'Clean';
    const FRAUD_STATUS_FRAUD    = 'Fraud';
    const FRAUD_STATUS_REVIEW   = 'Review';
    const FRAUD_STATUS_ERROR    = 'Error';
    const FRAUD_STATUS_UNKNOWN  = '';

    /**
     * Order fraud status
     *
     * @var string
     *
     * @Column (type="string")
     */
    protected $xpaymentsFraudStatus = '';

    /**
     * Order fraud type (which system considered the transaction fraudulent)
     *
     * @var string
     *
     * @Column (type="string")
     */
    protected $xpaymentsFraudType = '';

    /**
     * Transaction with fraud check data
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $xpaymentsFraudCheckTransactionId = 0;

    /**
     * Fraud check data from transaction
     */
    protected $xpaymentsFraudCheckData = false;

    /**
     * Hash for X-Payments cards
     */
    protected $xpaymentsCards = null;

    /**
     * @return string
     */
    public function getXpaymentsFraudStatus()
    {
        return $this->xpaymentsFraudStatus;
    }

    /**
     * @param string $xpaymentsFraudStatus
     *
     * @return Order
     */
    public function setXpaymentsFraudStatus($xpaymentsFraudStatus)
    {
        $this->xpaymentsFraudStatus = $xpaymentsFraudStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getXpaymentsFraudType()
    {
        return $this->xpaymentsFraudType;
    }

    /**
     * @param string $xpaymentsFraudType
     *
     * @return Order
     */
    public function setXpaymentsFraudType($xpaymentsFraudType)
    {
        $this->xpaymentsFraudType = $xpaymentsFraudType;
        return $this;
    }

    /**
     * @return int
     */
    public function getXpaymentsFraudCheckTransactionId()
    {
        return $this->xpaymentsFraudCheckTransactionId;
    }

    /**
     * @param int $xpaymentsFraudCheckTransactionId
     *
     * @return Order
     */
    public function setXpaymentsFraudCheckTransactionId($xpaymentsFraudCheckTransactionId)
    {
        $this->xpaymentsFraudCheckTransactionId = $xpaymentsFraudCheckTransactionId;
        return $this;
    }

    /**
     * Get fraud check data from transaction
     *
     * @return array
     */
    public function getXpaymentsFraudCheckData()
    {
        if (empty($this->xpaymentsFraudCheckData)) {

            if ($this->getXpaymentsFraudCheckTransactionId()) {

                $transaction = \XLite\Core\Database::getRepo('XLite\Model\Payment\Transaction')->find(
                    $this->getXpaymentsFraudCheckTransactionId()
                );

                if ($transaction) {
                    $this->xpaymentsFraudCheckData = $transaction->getXpaymentsFraudCheckData();
                }
            }
        }

        return $this->xpaymentsFraudCheckData;
    }

    /**
     * Return anchor name for the information about fraud check on the order details page
     *
     * @return string
     */
    public function getXpaymentsFraudInfoAnchor()
    {
        return 'fraud-info-' . $this->getXpaymentsFraudType();
    }

    /**
     * Is order fraud
     *
     * @return bool
     */
    public function isFraudStatus()
    {
        $result = false;
        $fraudStatuses = [
            FraudCheckData::RESULT_MANUAL,
            FraudCheckData::RESULT_PENDING,
            FraudCheckData::RESULT_FAIL,
        ];
        $fraudCheckData = $this->getXpaymentsFraudCheckData();

        if ($fraudCheckData) {
            foreach ($fraudCheckData as $item) {
                if (in_array($item->getResult(), $fraudStatuses)) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get X-Payments cards
     *
     * @return array
     */
    public function getXpaymentsCards()
    {
        if (null === $this->xpaymentsCards) {

            $transactions = $this->getPaymentTransactions();

            $this->xpaymentsCards = array();

            $adminUrl = \XLite\Module\XPay\XPaymentsCloud\Main::getClient()->getAdminUrl();

            foreach ($transactions as $transaction) {

                if (
                    !$transaction->isXpayments()
                    || !$transaction->getDataCell('xpaymentsCardNumber')
                    || !$transaction->getDataCell('xpaymentsCardType')
                    || !$transaction->getDataCell('xpaymentsCardExpirationDate')
                ) {
                    continue;
                }

                $card = array(
                    'cardNumber' => $transaction->getDataCell('xpaymentsCardNumber')->getValue(),
                    'cardType'   => $transaction->getDataCell('xpaymentsCardType')->getValue(),
                    'expire'     => $transaction->getDataCell('xpaymentsCardExpirationDate')->getValue(),
                    'xpid'       => $transaction->getXpaymentsId(),
                    'url'        => $adminUrl . '?target=payment&xpid=' . $transaction->getXpaymentsId(),
                );

                $card['cssType'] = strtolower($card['cardType']);

                $this->xpaymentsCards[] = $card;
            }
        }

        return $this->xpaymentsCards;
    }

    /**
     * Get calculated payment status
     *
     * @param boolean $override Override calculation cache OPTIONAL
     *
     * @return string
     */
    public function getCalculatedPaymentStatus($override = false)
    {
        $result = parent::getCalculatedPaymentStatus($override);

        if (PaymentStatus::STATUS_QUEUED == $result) {

            /** @var Transaction $lastTransaction */
            $lastTransaction = $this->getPaymentTransactions()->last();

            if ($lastTransaction->isXpayments()) {
                $backendTransactions = $lastTransaction->getBackendTransactions();
                if (
                    1 == count($backendTransactions)
                    && BackendTransaction::TRAN_TYPE_ACCEPT == $backendTransactions->last()->getType()
                ) {
                    $result = $lastTransaction->getOrder()->getPaymentStatus();
                }
            }
        }

        return $result;
    }

}
