<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\FacebookMarketing\View\Product\Details\Customer;

/**
 * @Decorator\Depend("XC\ProductVariants")
 */
class SkuProductVariants extends \XLite\Module\XC\FacebookMarketing\View\Product\Details\Customer\Sku implements \XLite\Base\IDecorator
{
    /**
     * @return string
     */
    protected function getFacebookPixelProductSku()
    {
        $productVariant = $this->getProductVariant();

        return $productVariant
            ? ($productVariant->getSku() ?: $productVariant->getVariantId())
            : $this->getProduct()->getSku();
    }
}
