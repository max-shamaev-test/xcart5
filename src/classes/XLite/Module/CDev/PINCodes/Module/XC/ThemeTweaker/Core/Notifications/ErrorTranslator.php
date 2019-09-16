<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\PINCodes\Module\XC\ThemeTweaker\Core\Notifications;


/**
 * ErrorTranslator
 *
 * @Decorator\Depend("XC\ThemeTweaker")
 */
class ErrorTranslator extends \XLite\Module\XC\ThemeTweaker\Core\Notifications\ErrorTranslator implements \XLite\Base\IDecorator
{
    protected static function getSuitabilityErrors()
    {
        $errors = parent::getSuitabilityErrors();

        $errors['order']['no_pin_codes'] = 'Order #{{value}} doesn\'t have any associated pin codes';

        return $errors;
    }
}