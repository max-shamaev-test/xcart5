<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2017-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/module-marketplace-terms-of-use.html for license details.
 */

namespace XLite\Module\QSL\SpecialOffersBase\Module\CDev\Coupons\Logic\Order\Modifier;

/**
 * Value discount modifier
 * 
 * @Decorator\Depend ({"!CDev\VolumeDiscounts", "CDev\Coupons"})
 */
class Discount extends \XLite\Module\CDev\Coupons\Logic\Order\Modifier\Discount
               implements \XLite\Base\IDecorator
{
    /**
     * Calculate
     *
     * @return \XLite\Model\Order\Surcharge
     */
    public function calculate()
    {
        $surcharge = null;

        $total = 0;

        foreach ($this->getUsedCoupons() as $used) {
            if ($used->getCoupon()) {
                $used->setValue($used->getCoupon()->getAmount($this->order));
            }
            $total += $used->getValue();

            if ($used->getCoupon()) {
                $this->distributeDiscountAmongItems(
                    $used->getValue(),
                    $this->getOrder()->getValidItemsByCoupon($used->getCoupon())
                );
            }
        }

        if ($this->isValidTotal($total)) {
            $total = min($total, $this->getSpecialOfferSubtotal());
            $surcharge = $this->addOrderSurcharge($this->code, $total * -1, false);
        }

        return $surcharge;
    }

    /**
     * Returns discount condition
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getDiscountCondition()
    {
        $cnd = parent::getDiscountCondition();

        $cnd->{\XLite\Module\CDev\VolumeDiscounts\Model\Repo\VolumeDiscount::P_SUBTOTAL}
            = $this->getSpecialOfferSubtotal();

        return $cnd;
    }
    
    /**
     * Returns the order subtotal plus order item surcharges.
     * 
     * @return float
     */
    protected function getSpecialOfferSubtotal()
    {
        return $this->getOrder()->getSpecialOffersSubtotal();
    }

}
