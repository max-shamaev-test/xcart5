<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\PINCodes\Core\Notifications;


use XLite\Core\Database;
use XLite\Model\OrderItem;

class DatPreProcessor
{
    /**
     * @param       $dir
     * @param array $data
     *
     * @return array
     */
    public static function prepareDataForNotification($dir, array $data)
    {
        if ($dir === 'modules/CDev/PINCodes/acquire_pin_codes_failed') {
            $data = static::preparePinCodesAcquireFailedNotificationData($data);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected static function preparePinCodesAcquireFailedNotificationData(array $data)
    {
        if (
            !empty($data['order'])
            && $data['order'] instanceof \XLite\Model\Order
        ) {
            /* @var \XLite\Module\CDev\PINCodes\Model\Order $order */
            $order = $data['order'];
            Database::getEM()->detach($order);
            $data['items'] = array_map(function (OrderItem $item) {
                /* @var \XLite\Module\CDev\PINCodes\Model\OrderItem $item*/
                if (!$item->countMissingPinCodes() && $item->countPinCodes()) {
                    $item->setAmount($item->getAmount() + rand(1, 3));
                }

                return $item;
            }, $order->getItems()->toArray());
        }

        return $data;
    }
}