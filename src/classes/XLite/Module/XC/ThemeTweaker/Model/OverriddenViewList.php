<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\ThemeTweaker\Model;

/**
 * Overridden view list
 *
 * @Entity
 * @Table  (name="overridden_view_lists")
 */
class OverriddenViewList extends \XLite\Model\AEntity
{
    /**
     * List key data hash
     *
     * @var string
     *
     * @Id
     * @Column (type="string", length=64)
     */
    protected $listHash;

    /**
     * List data
     *
     * @var string
     *
     * @Column (type="text")
     */
    protected $listData = '';

    /**
     * @return string
     */
    public function getListHash()
    {
        return $this->listHash;
    }

    /**
     * @return string
     */
    public function getListData()
    {
        return $this->listData;
    }

    /**
     * @param string $listData
     */
    public function setListData($listData)
    {
        $this->listData = $listData;
    }

    /**
     * Get temporary view list from list data
     *
     * @return \XLite\Model\ViewList
     */
    public function getTemporaryViewList()
    {
        $tempViewList = new \XLite\Model\ViewList();
        $listData = @unserialize($this->getListData());

        if ($listData) {
            $tempViewList->map(
                $listData
            );
        }

        return $tempViewList;
    }
}
