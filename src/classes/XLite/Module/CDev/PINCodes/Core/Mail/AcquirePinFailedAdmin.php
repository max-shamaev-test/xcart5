<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\PINCodes\Core\Mail;


use XLite\Model\Order;
use XLite\Module\CDev\PINCodes\Model\OrderItem;

class AcquirePinFailedAdmin extends \XLite\Core\Mail\Order\AAdmin
{
    static function getDir()
    {
        return 'modules/CDev/PINCodes/acquire_pin_codes_failed';
    }

    protected static function defineVariables()
    {
        return [
                'backordered_item_names' => '',
            ] + parent::defineVariables();
    }

    public function __construct(Order $order)
    {
        parent::__construct($order);

        $backorderedItems = [];
        $backorderedProducts = [];

        foreach ($order->getItems() as $item) {
            /* @var OrderItem $item */
            if ($item->countMissingPinCodes()) {
                $backorderedItems[] = $item->getName();
                $backorderedProducts[] = $item->getProduct();
            }
        }

        $this->populateVariables([
            'backordered_item_names' => implode(', ', $backorderedItems),
        ]);

        $this->appendData([
            'items'               => $order->getItems(),
            'backorderedProducts' => $backorderedProducts,
        ]);
    }
}