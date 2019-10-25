<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Model;

/**
 * Fake order item for zero auth's and recharges from X-Paymemts.
 * Something customer can not put into his cart
 */
class OrderItem extends \XLite\Model\OrderItem implements \XLite\Base\IDecorator
{
    /**
     * Flag for zero auth and recharges
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $xpaymentsEmulated = false;

    /**
     * Is this item a fake one for zero auth and recharges
     *
     * @return boolean
     */
    public function isXpaymentsEmulated()
    {
        return $this->getXpaymentsEmulated();
    }

    /**
     * Check if item is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->isXpaymentsEmulated()
            || parent::isValid();
    }

    /**
     * Deleted Item flag
     *
     * @return boolean
     */
    public function isDeleted()
    {
        $result = parent::isDeleted();

        if ($this->isXpaymentsEmulated()) {
            $result = false;
        }

        return $result;
    }

    /**
     * Returns deleted product for fake items
     *
     * @return \XLite\Model\Product
     */
    public function getProduct()
    {
        if ($this->isXpaymentsEmulated()) {
            return $this->getDeletedProduct();
        } else {
            return parent::getProduct();
        }
    }

    /**
     * Returns deleted product for fake items
     *
     * @return \XLite\Model\Product
     */
    public function getObject()
    {
        if ($this->isXpaymentsEmulated()) {
            return $this->getDeletedProduct();
        } else {
            return parent::getObject();
        }
    }

    /**
     * Check if the item is valid to clone through the Re-order functionality
     *
     * @return boolean
     */
    public function isValidToClone()
    {
        if ($this->isXpaymentsEmulated()) {

            $result = false;

        } else {

            $result = parent::isValidToClone();
        }

        return $result;
    }

    /**
     * Set xpaymentsEmulated
     *
     * @param boolean $xpaymentsEmulated
     * @return OrderItem
     */
    public function setXpaymentsEmulated($xpaymentsEmulated)
    {
        $this->xpaymentsEmulated = $xpaymentsEmulated;
        return $this;
    }

    /**
     * Get xpaymentsEmulated
     *
     * @return boolean
     */
    public function getXpaymentsEmulated()
    {
        return $this->xpaymentsEmulated;
    }

    /**
    * Get item clear price. This value is used as a base item price for calculation of netPrice
    *
    * @return float
    */
    public function getClearPrice()
    {
        if ($this->isXpaymentsEmulated()) {
            return parent::getPrice();
        } else {
            return parent::getClearPrice();
        }
    }
}
