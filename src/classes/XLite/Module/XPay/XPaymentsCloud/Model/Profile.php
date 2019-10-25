<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Model;

/**
 * X-Payments Specific fields
 *
 */
class Profile extends \XLite\Model\Profile implements \XLite\Base\IDecorator
{
    /**
     * X-Payments Customer Id
     * 
     * @var string
     *
     * @Column (type="string")
     */
    protected $xpaymentsCustomerId = '';

    /**
     * Hash of X-Payments cards
     *
     * @var array
     */
    protected $cards = null;

    /**
     * Returns X-Payments Customer Id
     * 
     * @return string
     */
    public function getXpaymentsCustomerId()
    {
        return $this->xpaymentsCustomerId;
    }

    /**
     * Set X-Payments Customer Id
     *
     * @param $xpaymentsCustomerId
     *
     * @return Profile
     */
    public function setXpaymentsCustomerId($xpaymentsCustomerId)
    {
        $this->xpaymentsCustomerId = $xpaymentsCustomerId;

        return $this;
    }

    /**
     * Get SDK Client
     *
     * @return \XPaymentsCloud\Client
     */
    protected function getXpaymentsClient()
    {
        return \XLite\Module\XPay\XPaymentsCloud\Core\ApiClient::getInstance()->getClient();
    }

    /**
     * Get X-Payments Saved cards
     *
     * @return array
     */
    public function getXpaymentsCards()
    {
        if (null === $this->cards) { 

            $this->cards = array();

            if (
                $this->getXpaymentsClient()
                && $this->getXpaymentsCustomerId()
            ) {

                try {

                    $this->cards = $this->getXpaymentsClient()
                        ->doGetCustomerCards($this->getXpaymentsCustomerId())
                        ->customer_cards;

                    foreach ($this->cards as &$card) {
                        $card['cssType'] = strtolower($card['type']);
                        $card['cardNumber'] = sprintf('%s******%s', $card['first6'], $card['last4']);
                        $card['expire'] = sprintf('%s/%s', $card['expireMonth'], $card['expireYear']);
                    }

                } catch (\Exception $exception) {

                    \XLite\Logger::getInstance()->logCustom('XPaymentsCloud', $exception->getMessage());
                }
            }
        }

        return $this->cards;
    }

    /**
     * Update default X-Payments saved card
     *
     * @param string $cardId ID of the card
     *
     * @return bool
     */
    public function setXpaymentsDefaultCard($cardId)
    {
        $result = false;

        if (
            $this->getXpaymentsClient()
            && $this->getXpaymentsCustomerId()
        ) {

            try {

                $response = $this->getXpaymentsClient()
                    ->doSetDefaultCustomerCard($this->getXpaymentsCustomerId(), $cardId);

                $result = (bool)$response->result;

            } catch (\Exception $exception) {

                \XLite\Logger::getInstance()->logCustom('XPaymentsCloud', $exception->getMessage());
            }
        }

        return $result;
    }

    /**
     * Remove X-Payments saved card
     *
     * @param string $cardId ID of the card
     *
     * @return bool 
     */
    public function removeXpaymentsCard($cardId)
    {
        $result = false;

        if (
            $this->getXpaymentsClient()
            && $this->getXpaymentsCustomerId()
        ) {

            try {

                $response = $this->getXpaymentsClient()
                    ->doDeleteCustomerCard($this->getXpaymentsCustomerId(), $cardId);

                $result = (bool)$response->result;

            } catch (\Exception $exception) {

                \XLite\Logger::getInstance()->logCustom('XPaymentsCloud', $exception->getMessage());
            }
        }

        return $result;
    }
}
