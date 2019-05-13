<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\Module\XC\MultiVendor\Model;

/**
 * Class represents an order
 *
 * @Decorator\Depend({"CDev\XPaymentsConnector","XC\MultiVendor"})
 */
class Order extends \XLite\Model\Order implements \XLite\Base\IDecorator
{
    /**
     * Cart is not parent in case if is_zero_auth is true
     *
     * @return boolean
     */
    public function isParent()
    {
        return ($this->isZeroAuth()) ? false : parent::isParent();
    }
}
