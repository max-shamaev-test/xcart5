<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\Paypal\View\Button\Checkout;

/**
 * Express Checkout base button
 *
 * @ListChild (list="checkout.review.selected.placeOrder", weight="450")
 * @ListChild (list="checkout_fastlane.sections.place-order.before", weight="100")
 */
class ExpressCheckout extends \XLite\Module\CDev\Paypal\View\Button\AExpressCheckout
{
    public function getJSFiles()
    {
        return array_merge(parent::getJSFiles(), [
            'modules/CDev/Paypal/button/js/checkout.js'
        ]);
    }

    protected function getButtonClass()
    {
        return parent::getButtonClass() . ' paypal-ec-checkout';
    }
}
