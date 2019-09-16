<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\Paypal\Core;

/**
 * Session
 */
class Session extends \XLite\Core\Session implements \XLite\Base\IDecorator
{
    /**
     * Restore session ID from USER1 parameter of the PayPal response
     */
    protected function detectPublicSession()
    {
        list($session, $source) = parent::detectPublicSession();

        if (!$session) {

            $request = \XLite\Core\Request::getInstance();

            if (
                'POST' == $request->getRequestMethod()
                && 'payment_return' == $request->target
                && ($sid = $request->USER1)
            ) {
                $session = $this->loadSession(trim($sid));
                $source = 'POST';
            }
        }

        return array($session, $source);
    }
}
