<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2017-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/module-marketplace-terms-of-use.html for license details.
 */

namespace XLite\Module\QSL\SpecialOffersBase\Model;

use XLite\Core\Database;

/**
 * Decorated order model
 */
class Order extends \XLite\Model\Order implements \XLite\Base\IDecorator
{
    /**
     * Returns the order subtotal including per-item surcharges.
     *
     * @return float
     */
    public function getSpecialOffersSubtotal()
    {
        return $this->getSubtotal()
            + Database::getInstance()->getRepo('XLite\Model\OrderItem')
                ->getSpecialOffersOrderItemSurchargesSum($this);
    }

    /**
     * Renew shipping method
     *
     * @return void
     */
    public function renewShippingMethod()
    {
        parent::renewShippingMethod();
        $this->renewShippingMethodSpecialOffersWorkaround();
    }

    /**
     * Fixes the wrong shipping cost calculation when assembling the cart
     * fingerprint.
     *
     * The problem there is \XLite\Model\Order::updateOrder() calling the
     * renewShippingMethod() method before all order modifiers are calculated
     * for the cart. As the result the renewShippingMethod() method calls the
     * \XLite\Logic\Order\Modifier\Shipping::getSelectedRate() method that
     * calculates a wrong shipping cost (without the special offers discount)
     * and caches this wrong value in one of its protected object properties.
     * Later, when calculating the "shippingTotal" part of the cart fingerprint,
     * assembleEvent() uses \XLite\Model\Order::getFingerprintByShippingTotal()
     * method that calls getSelectedRate() and receives the wrong cached value.
     *
     * So, to fix this problem we implement a workaround: we clear the cached
     * selected rate before calculating the cart fingerprint.
     *
     * @return void
     */
    protected function renewShippingMethodSpecialOffersWorkaround()
    {
        $modifier = $this->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');
        $modifier ? $modifier->setSelectedRate(null) : null;
    }
}
