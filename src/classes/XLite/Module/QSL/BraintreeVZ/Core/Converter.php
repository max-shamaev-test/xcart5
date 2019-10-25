<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\BraintreeVZ\Core;

/**
 * Miscellaneous conversion routines
 */
class Converter extends \XLite\Core\Converter implements \XLite\Base\IDecorator
{
    /**
     * Round price for Paypal
     *
     * @param float $value
     *
     * @return float
     */
    public static function preparePaypalPrice($value)
    {
        return round($value, 2);
    }

}
