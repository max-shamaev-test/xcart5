<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\Paypal\View\Button\ProductList;

/**
 * Express Checkout button
 */
class ExpressCheckout extends \XLite\Module\CDev\Paypal\View\Button\AExpressCheckout
{
    const PARAM_PRODUCT_ID = 'productId';

    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += [
            self::PARAM_PRODUCT_ID => new \XLite\Model\WidgetParam\TypeInt('Product id'),
        ];
    }

    protected function isVisible()
    {
        return parent::isVisible() && \XLite\Module\CDev\Paypal\Main::isBuyNowEnabled();
    }

    public function getJSFiles()
    {
        return array_merge(parent::getJSFiles(), [
            'modules/CDev/Paypal/button/js/product_list.js'
        ]);
    }

    protected function getButtonClass()
    {
        return parent::getButtonClass() . ' pp-style-buynow pp-ec-product';
    }

    protected function getButtonAdditionalParams()
    {
        return parent::getButtonAdditionalParams() + [
                'data-product-id' => $this->getParam(static::PARAM_PRODUCT_ID),
            ];
    }
}
