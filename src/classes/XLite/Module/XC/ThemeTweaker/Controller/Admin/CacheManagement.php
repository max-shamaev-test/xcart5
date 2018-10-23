<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\ThemeTweaker\Controller\Admin;


class CacheManagement extends \XLite\Controller\Admin\CacheManagement implements \XLite\Base\IDecorator
{
    /**
     * Export action
     *
     * @return void
     */
    protected function doActionRebuildViewLists()
    {
        $repo = \XLite\Core\Database::getRepo('XLite\Model\ViewList');
        $overriddenData = $repo->findOverriddenData();
        \XLite\Core\Database::getRepo('XLite\Module\XC\ThemeTweaker\Model\OverriddenViewList')->replaceOverriddenData($overriddenData);

        parent::doActionRebuildViewLists();

        $overriddenLists = \XLite\Core\Database::getRepo('XLite\Module\XC\ThemeTweaker\Model\OverriddenViewList')->findAll();
        foreach ($overriddenLists as $overriddenList) {
            $tempList = $overriddenList->getTemporaryViewList();

            if ($list = $repo->findEqual($tempList)) {
                $list->mapOverrides($tempList);
            }
        }

        \XLite\Core\Database::getEM()->flush();
    }
}