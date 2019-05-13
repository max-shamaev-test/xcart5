<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

return function () {

    // 1. Find all fake X-Payments payment methods and delete them

    $qb = \XLite\Core\Database::getEM()->createQueryBuilder();

    $qb->delete('XLite\Model\Payment\Method', 'pm')
        ->where('pm.moduleName = :moduleName')
        ->andWhere('pm.service_name like :service_name')
        ->setParameters([
            'moduleName'   => 'CDev_XPaymentsConnector',
            'service_name' => '%XPayments.Allowed%',
        ]);

    $qb->getQuery()->execute();

    // 2. Change prefix of service_name of the rest of X-Payments payment methods from "XPayments" to "XPayments.Allowed"

    $realMethods = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
        ->findBy([
            'moduleName' => 'CDev_XPaymentsConnector',
        ]);

    foreach ($realMethods as $key => $pm) {
        $serviceName = $pm->getServiceName();

        if (false === strpos($serviceName, 'XPayments.Allowed')) {
            $serviceName = str_replace('XPayments', 'XPayments.Allowed', $serviceName);
            $pm->setServiceName($serviceName);
            \XLite\Core\Database::getEM()->persist($pm);
        }

    }

    \XLite\Core\Database::getEM()->flush();

    // 3. Parse X-Payments payment methods from marketplace

    $url = \Includes\Utils\ConfigParser::getOptions(array('marketplace', 'url'));

    if ($url) {

        $suffix   = sprintf('/sites/default/files/pm-%s.json', \XLite::getInstance()->getMajorVersion());
        $url      = preg_replace('/\/[^\/]+$/US', $suffix, str_replace('https://', 'http://', $url));
        $request  = new \XLite\Core\HTTP\Request($url);
        $response = $request->sendRequest();

        if (!empty($response->body)) {
            $data = json_decode($response->body, true);

            if (!empty($data) && is_array($data)) {
                $fakeMethods = $data;
            }
        }
    }

    if (empty($fakeMethods)) {
        $paymentMethodsData = file_get_contents(__DIR__ . LC_DS . 'pm-5.3.json');
        $fakeMethods = json_decode($paymentMethodsData, true);
    }

    // 4. Check if payment method already exists in database, update existing method or add new one

    foreach ($fakeMethods as $keyFake => $pmFake) {

        if ($pmFake['moduleName'] !== 'CDev_XPaymentsConnector') {
            // Skip non-XP methods
            continue;
        }

        $realMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy([
                'service_name' => $pmFake['service_name'],
            ]);

        if ($realMethod) {
            // Payment method already exists in database
            $realMethod->setAdminOrderBy($pmFake['orderby']);
            $realMethod->setCountries($pmFake['countries']);
            $realMethod->setIconURL($pmFake['iconURL']);

        } else {
            // Add new payment method
            $new = new \XLite\Model\Payment\Method;
            \XLite\Core\Database::getEM()->persist($new);
            $new->setServiceName($pmFake['service_name']);
            $new->setClass($pmFake['class']);
            $new->setType($pmFake['type']);
            $new->setModuleName($pmFake['moduleName']);
            $new->setAdminOrderby($pmFake['orderby']);
            $new->setCountries($pmFake['countries']);
            $new->setExCountries(isset($pmFake['exCountries']) ? $pmFake['exCountries'] : null);
            $new->setName($pmFake['translations'][0]['name']);
            $new->setAdded($pmFake['added']);
            $new->setEnabled($pmFake['enabled']);
            $new->setModuleEnabled($pmFake['moduleEnabled']);
            $new->setFromMarketplace($pmFake['fromMarketplace']);
            $new->setIconURL($pmFake['iconURL']);
        }
    }

    \XLite\Core\Database::getEM()->flush();

    // 5. Check if there is at least one PM that supports saving cards and fix "Saved card" payment method (e.g, for trial users)

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
