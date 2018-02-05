<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\CloudSearch\View\CloudFilters;


use XLite\Core\Database;
use XLite\Module\QSL\CloudSearch\Model\Repo\Product as ProductRepo;
use XLite\Module\QSL\CloudSearch\Model\Repo\Product;

/**
 * Cloud filters sidebar box widget
 *
 * @Decorator\Depend ({"QSL\ShopByBrand"})
 */
class FiltersBoxBrands extends \XLite\Module\QSL\CloudSearch\View\CloudFilters\FiltersBox implements \XLite\Base\IDecorator
{
    /**
     * Get brand search condition
     *
     * @return null|\XLite\Model\AEntity
     */
    protected function getBrand()
    {
        $conditions = $this->getCloudSearchConditions();

        $brandId = $conditions->{ProductRepo::P_BRAND_ID};

        return !$brandId ? null : Database::getRepo('XLite\Module\QSL\ShopByBrand\Model\Brand')
            ->find($brandId);
    }

    /**
     * Get designated brand attribute
     *
     * @return string
     */
    protected function getBrandAttribute()
    {
        return Database::getRepo('XLite\Model\Attribute')
            ->findBrandAttribute();
    }

    /**
     * Get commented widget data
     *
     * @return array
     */
    protected function getPhpToJsData()
    {
        $data = parent::getPhpToJsData();

        if ($this->getBrandAttribute()) {
            $brand          = $this->getBrand();
            $brandAttribute = $this->getBrandAttribute();

            $data['facetApi']['data'] += [
                'brand'          => $brand !== null ? $brand->getName() : null,
                'brandAttribute' => $brandAttribute->getName(),
            ];
        }

        return $data;
    }
}