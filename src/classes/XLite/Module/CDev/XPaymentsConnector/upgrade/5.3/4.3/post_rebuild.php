<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

return function () {
    
    // 1. Remove unnecessary setting from the database 

    $setting = \XLite\Core\Database::getRepo('XLite\Model\Config')
        ->findOneBy([
            'name' => 'xpc_allowed_ip_addresses',
        ]);

    if ($setting) {
        $setting->delete();
    }

    // 2. Fix the Saved Cards payment method

    $methods = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
        ->findBy([
            'class' => 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments',
        ]);

    $savingCardsEnabled = false;

    foreach ($methods as $pm) {
        if ('Y' === $pm->getSetting('saveCards')) {
            $savingCardsEnabled = true;
            break;
        }
    }

    if ($savingCardsEnabled) {
        $saveCardsMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy([
                'class' => 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\SavedCard',
            ]);

        if (false == $saveCardsMethod->getEnabled()) {
            $saveCardsMethod->setEnabled(true);
            $saveCardsMethod->setAdded(true);
            $saveCardsMethod->setModuleEnabled(true);
            $saveCardsMethod->setFromMarketplace(false);
            \XLite\Core\Database::getEM()->persist($saveCardsMethod);
        }
    }

    \XLite\Core\Database::getEM()->flush();
    \XLite\Core\Config::updateInstance();
};