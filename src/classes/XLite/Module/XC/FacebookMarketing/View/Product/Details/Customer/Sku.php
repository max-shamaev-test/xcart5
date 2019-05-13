<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\FacebookMarketing\View\Product\Details\Customer;

/**
 * @ListChild (list="product.details.page.info", weight="5")
 * @ListChild (list="product.details.quicklook.info", weight="5")
 */
class Sku extends \XLite\View\Product\Details\Customer\Widget
{
    /**
     * Return the specific widget service name to make it visible as specific CSS class
     *
     * @return null|string
     */
    public function getFingerprint()
    {
        return 'widget-fingerprint-sku';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/FacebookMarketing/product/details/sku/body.twig';
    }

    /**
     * @return string
     */
    protected function getFacebookPixelProductSku()
    {
        return $this->getProduct()->getSku();
    }
}
