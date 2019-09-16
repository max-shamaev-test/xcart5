<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\View\ItemsList\Model\Payment;

use XLite\Module\CDev\XPaymentsConnector\Core\Settings;

/**
 * Decorate methods items list. We should exclude duplicated XP payment methods here
 */
class OnlineMethods extends \XLite\View\ItemsList\Model\Payment\OnlineMethods implements \XLite\Base\IDecorator
{
    /**
     * Return payment methods list with excluded XP duplicates
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $data = parent::getData($cnd, false);

        $xpMethods = [];

        /** @var \XLite\Model\Payment\Method $pm */
        foreach ($data as $key => $pm) {
            if (Settings::XP_MODULE_NAME == $pm->getModuleName()) {
                if (!array_key_exists($pm->getServiceName(), $xpMethods)) {
                    $xpMethods[$pm->getServiceName()] = $pm;
                    $data[$key]->setName($pm->getOriginalName());
                } else {
                    unset($data[$key]);
                }
            }
        }

        return $countOnly
            ? count($data)
            : $data;
    }

}
