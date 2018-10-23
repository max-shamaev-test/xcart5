<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\ThemeTweaker\Model\Repo;

/**
 * Overridden view list repository
 */
class OverriddenViewList extends \XLite\Model\Repo\ARepo
{
    public function replaceOverriddenData($overriddenData)
    {
        foreach ($overriddenData as $data) {
            $listHash = md5($data['child'] . $data['tpl'])
                . md5($data['list'] . $data['zone'] . $data['weight']);
            unset($data['list_id']);
            $listData = serialize($data);

            $connection = \XLite\Core\Database::getEM()->getConnection();
            $query = 'REPLACE INTO ' . $this->getTableName()
                . ' set `listHash` = ?, `listData` = ?';

            $connection->executeUpdate($query, [$listHash, $listData]);
        }
    }
}
