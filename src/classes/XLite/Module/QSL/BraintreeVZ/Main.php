<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\BraintreeVZ;

/**
 * PayPal powered by Braintree payment gateway 
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Add record to the module log file
     *
     * @param string $message Text message OPTIONAL
     * @param mixed  $data    Data (can be any type) OPTIONAL
     *
     * @return void
     */
    public static function addLog($message = null, $data = null)
    {
        if ($message && $data) {
            $msg = array(
                'message' => $message,
                'data'    => $data,
            );

        } else {
            $msg = ($message ?: ($data ?: null));
        }

        \XLite\Logger::logCustom(
            self::getModuleName(),
            $msg
        );
    }

    /**
     * Get path of SDK classes file
     *
     * @return string
     */
    public static function getLibClassesFile()
    {
        return LC_DIR_MODULES . 'QSL' . LC_DS . 'BraintreeVZ' . LC_DS . 'lib' . LC_DS . 'autoload.php';
    }

    /**
     * Perform some actions at startup
     *
     * @return void
     */
    public static function init()
    {
        parent::init();

        require_once self::getLibClassesFile();
    }

}
