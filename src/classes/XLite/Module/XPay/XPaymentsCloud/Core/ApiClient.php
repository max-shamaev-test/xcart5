<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Core;

/**
 * XPayments API client
 */
class ApiClient extends \XLite\Base\Singleton
{
    /**
     * X-Payments SDK Client
     *
     * @var \XPaymentsCloud\Client
     */
    protected $client = null;

    /**
     * Get SDK Client
     *
     * @return \XPaymentsCloud\Client
     */
    public function getClient()
    {
        if (null === $this->client) {

            $this->client = false;

            try {

                require_once LC_DIR_MODULES . 'XPay' . LC_DS . 'XPaymentsCloud' . LC_DS . 'lib' . LC_DS . 'XPaymentsCloud' . LC_DS . 'Client.php';

                $paymentMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
                    ->findOneBy(array('service_name' => 'XPaymentsCloud'));

                if ($paymentMethod) {

                    $this->client = new \XPaymentsCloud\Client(
                        $paymentMethod->getSetting('account'),
                        $paymentMethod->getSetting('api_key'),
                        $paymentMethod->getSetting('secret_key')
                    );
                }

            } catch (\Exception $exception) {

                \XLite\Logger::getInstance()->logCustom('XPaymentsCloud', $exception->getMessage());
            }
        }

        return $this->client;
    }
}
