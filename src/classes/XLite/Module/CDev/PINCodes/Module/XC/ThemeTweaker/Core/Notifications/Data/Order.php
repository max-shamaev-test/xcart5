<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\PINCodes\Module\XC\ThemeTweaker\Core\Notifications\Data;


/**
 * Order
 *
 * @Decorator\Depend("XC\ThemeTweaker")
 */
class Order extends \XLite\Module\XC\ThemeTweaker\Core\Notifications\Data\Order implements \XLite\Base\IDecorator
{
    protected function getTemplateDirectories()
    {
        return array_merge(parent::getTemplateDirectories(), [
            'modules/CDev/PINCodes/acquire_pin_codes_failed'
        ]);
    }

    public function getSuitabilityErrors($templateDir)
    {
        $errors = parent::getSuitabilityErrors($templateDir);

        /** @var \XLite\Module\CDev\PINCodes\Model\Order $order */
        $order = $this->getOrder($templateDir);

        if (
            $templateDir === 'modules/CDev/PINCodes/acquire_pin_codes_failed'
            && $order
            && !$order->hasPinCodes()
        ) {
            $errors[] = [
                'code'  => 'no_pin_codes',
                'value' => $order->getOrderNumber(),
                'type'  => 'error'
            ];
        }

        return $errors;
    }
}