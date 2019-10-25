<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\ProductVariants\Core;

/**
 * Class QuickData
 */
class QuickData extends \XLite\Core\QuickData implements \XLite\Base\IDecorator
{
    /**
     * Get memberships
     *
     * @param \XLite\Model\Product $product    Product
     * @param mixed                $membership Membership
     *
     * @return \XLite\Model\QuickData
     */
    public function updateData(\XLite\Model\Product $product, $membership)
    {
        $data = parent::updateData($product, $membership);

        if ($product->hasVariants()) {
            $minPrice = min($data->getPrice(), $product->getQuickDataMinPrice());
            $data->setMinPrice(\XLite::getInstance()->getCurrency()->roundValue($minPrice));

            $maxPrice = max($data->getPrice(), $product->getQuickDataMaxPrice());
            $data->setMaxPrice(\XLite::getInstance()->getCurrency()->roundValue($maxPrice));
        } else {
            $data->setMinPrice(\XLite::getInstance()->getCurrency()->roundValue($data->getPrice()));
            $data->setMaxPrice(\XLite::getInstance()->getCurrency()->roundValue($data->getPrice()));
        }

        return $data;
    }
}