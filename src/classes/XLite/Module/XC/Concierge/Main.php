<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Concierge;

use XLite\Module\XC\Concierge\Core\Mediator;

abstract class Main extends \XLite\Module\AModule
{
    public static function init()
    {
        parent::init();

        $additionalConfig = LC_DIR_MODULES . 'XC' . LC_DS . 'Concierge' . LC_DS . 'config.yaml';
        if (\XLite\Core\Config::getInstance()->XC->Concierge->additional_config_loaded !== 'true'
            && \Includes\Utils\FileManager::isFileReadable($additionalConfig)
        ) {
            \XLite\Core\Database::getInstance()->loadFixturesFromYaml($additionalConfig);
            \XLite\Core\Config::updateInstance();
        }

        if (Mediator::getInstance()->isConfigured()) {
            register_shutdown_function([Mediator::getInstance(), 'handleShutdown']);

            set_exception_handler(function ($exception) {
                Mediator::getInstance()->handleException($exception);
                \Includes\ErrorHandler::handleException($exception);
            });
        }
    }
}
