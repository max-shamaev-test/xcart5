<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

return new \XLite\Rebuild\Hook(
    function () {
        $overriddenData = \XLite\Core\Database::getRepo('XLite\Model\ViewList')->findOverriddenData();
        \XLite\Core\Database::getRepo('XLite\Module\XC\ThemeTweaker\Model\OverriddenViewList')->replaceOverriddenData($overriddenData);

        $overriddenLists = \XLite\Core\Database::getRepo('XLite\Module\XC\ThemeTweaker\Model\OverriddenViewList')->findAll();
        if ($overriddenLists) {
            foreach ($overriddenLists as $overriddenList) {
                $tempList = $overriddenList->getTemporaryViewList();
                $entity   = \XLite\Core\Database::getRepo('XLite\Model\ViewList')->findEqual($tempList, true);

                if ($entity) {
                    $entity->mapOverrides($tempList);
                }
            }
        }
    }
);
