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
     * X-Payments SDK Client
     *
     * @var \XPaymentsCloud\Client
     */
    private static $client = null;

    /**
     * X-Payments Cloud payment method
     *
     * @var \XLite\Model\Payment\Method
     */
    private static $method = null;

    /**
     * Return link to settings form
     *
     * @return string
     */
    public static function getSettingsForm()
    {
        $paymentMethod = static::getPaymentMethod();

        return \XLite\Core\Converter::buildURL(
            'payment_method',
            '',
            ['method_id' => $paymentMethod->getMethodId()]
        );
    }

    /**
     * Get SDK Client
     *
     * @return \XPaymentsCloud\Client
     */
    public static function getClient()
    {
        if (is_null(static::$client)) {

            static::$client = false;

            try {

                require_once LC_DIR_MODULES . 'XPay' . LC_DS . 'XPaymentsCloud' . LC_DS . 'lib' . LC_DS . 'XPaymentsCloud' . LC_DS . 'Client.php';

                $paymentMethod = static::getPaymentMethod();

                if ($paymentMethod) {

                    static::$client = new \XPaymentsCloud\Client(
                        $paymentMethod->getSetting('account'),
                        $paymentMethod->getSetting('api_key'),
                        $paymentMethod->getSetting('secret_key')
                    );
                }

            } catch (\Exception $exception) {

                \XLite\Logger::getInstance()->logCustom('XPaymentsCloud', $exception->getMessage());
            }
        }

        return static::$client;
    }

    /**
     * Get X-Payments Cloud payment method
     *
     * @return \XLite\Model\Payment\Method
     */
    public static function getPaymentMethod()
    {
        if (is_null(static::$method)) {
            static::$method = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                ->findOneBy(['service_name' => 'XPaymentsCloud']);
        }
        return static::$method;
    }

    /**
     * Logs error in X-PaymentsCloud log file
     *
     * @param string $message Log message
     */
    public static function log($message)
    {
        \XLite\Logger::getInstance()->logCustom('XPaymentsCloud', $message);
    }

}
