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
}
