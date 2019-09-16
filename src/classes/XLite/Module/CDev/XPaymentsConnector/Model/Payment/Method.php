<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\Model\Payment;

use XLite\Module\CDev\XPaymentsConnector\Core\Settings;

class Method extends \XLite\Model\Payment\Method implements \XLite\Base\IDecorator
{
    /**
     * The moduleEnabled field is assumed as always true for XP payment methods
     *
     * @return boolean
     */
    public function isModuleEnabled()
    {
        $classes = [
            'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments',
            'Module\CDev\XPaymentsConnector\Model\Payment\Processor\SavedCard',
        ];

        if (in_array($this->getClass(), $classes)) {
            $result = true;
        } else {
            $result = parent::isModuleEnabled();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        $serviceName = $this->getServiceName();
        $class = str_replace(Settings::XP_ALLOWED_PREFIX . '.', '', $serviceName);

        $origName = array_search($class, Settings::getInstance()->modulesMap);

        if (!$origName) {
            $origName = $this->getName();
        }

        return $origName;
    }

    /**
     * Make payment method "fake" one
     *
     * @return void
     */
    public function makeMethodFake()
    {
        $this->setFromMarketplace(true);
        $this->setAdded(false);
        $this->setEnabled(false);
        $this->setName($this->getOriginalName());
    }

}
