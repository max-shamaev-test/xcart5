<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\PINCodes\Core;

use XLite\Module\CDev\PINCodes\Core\Mail\AcquirePinFailedAdmin;

/**
 * Mailer
 *
 */
abstract class Mailer extends \XLite\Core\Mailer implements \XLite\Base\IDecorator
{
    /**
     * @param \XLite\Model\Order $order Order model
     */
    public static function sendAcquirePinCodesFailedAdmin(\XLite\Model\Order $order)
    {
        (new AcquirePinFailedAdmin($order))->schedule();
    }
}
