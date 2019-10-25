<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Core\Mail\Order;


use XLite\Model\Order;

class BackorderCreatedAdmin extends \XLite\Core\Mail\Order\AAdmin
{
    static function getDir()
    {
        return 'backorder_created';
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
            if (0 < $item->getBackorderedAmount()) {
                $backorderedItems[]    = $item->getName();
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