<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2017-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/module-marketplace-terms-of-use.html for license details.
 */

namespace XLite\Module\QSL\SpecialOffersBase\Module\CDev\VolumeDiscounts\Model;

use XLite\Model\Order;

/**
 * Decorated coupon model
 * 
 * @Decorator\Depend ("CDev\VolumeDiscounts")
 */
class VolumeDiscount extends \XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount implements \XLite\Base\IDecorator
{
    /**
     * Get discount amount
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return float
     */
    public function getAmount(Order $order)
    {
        $base = $this->getSpecialOffersDiscountBase($order);

        $discount = $this->isAbsolute()
            ? $this->getValue()
            : ($base * $this->getValue() / 100);

        return min($discount, $base);
    }

    /**
     * Get the base sum from which the discount is calculated.
     *
     * @param \XLite\Model\Order $order Order
     * 
     * @return float
     */
    protected function getSpecialOffersDiscountBase(Order $order)
    {
        return $order->getSpecialOffersSubtotal();
    }

}