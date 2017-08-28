<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\ProductVariants\View\FormModel\Product;

use XLite\Core\Database;

class Info extends \XLite\View\FormModel\Product\Info implements \XLite\Base\IDecorator
{
    /**
     * @param array $sections
     *
     * @return array
     */
    protected function prepareFields($sections)
    {
        $result = parent::prepareFields($sections);

        $priceDescription = $this->getDataObject()->default->identity && $this->getPriceDescriptionTemplate()
            ? $this->getWidget([
                'template' => $this->getPriceDescriptionTemplate(),
            ])->getContent()
            : '';

        $result['prices_and_inventory']['price']['description'] = $priceDescription;

        return $result;
    }

    /**
     * @return string
     */
    protected function getPriceDescriptionTemplate()
    {
        /** @var \XLite\Module\XC\ProductVariants\Model\Product $product */
        $product = $this->getProductEntity();

        if ($product && $product->hasVariants()) {
            return 'modules/XC/ProductVariants/form_model/product/info/variants_link.twig';
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getVariantsPageUrl()
    {
        $identity = $this->getDataObject()->default->identity;

        return $this->buildURL(
            'product',
            '',
            [
                'product_id' => $identity,
                'page'       => 'variants',
            ]
        );
    }

    /**
     * @return int|string
     */
    protected function getVariantsCount()
    {
        /** @var \XLite\Module\XC\ProductVariants\Model\Product $product */
        $product = $this->getProductEntity();

        return $product
            ? count($product->getVariants())
            : '';
    }
}
