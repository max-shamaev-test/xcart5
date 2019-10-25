<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud;

class Main extends \XLite\Module\AModule
{
    /**
     * Return link to settings form
     *
     * @return string
     */
    public static function getSettingsForm()
    {
        $paymentMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(['service_name' => 'XPaymentsCloud']);

        return \XLite\Core\Converter::buildURL(
            'payment_method',
            '',
            ['method_id' => $paymentMethod->getMethodId()]
        );
    }

}
