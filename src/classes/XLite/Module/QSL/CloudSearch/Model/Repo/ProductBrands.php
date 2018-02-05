<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\CloudSearch\Model\Repo;

use Doctrine\ORM\QueryBuilder;
use XLite\Core\CommonCell;
use XLite\Core\Database;

/**
 * The "product" repo class
 *
 * @Decorator\Depend({"QSL\ShopByBrand", "QSL\CloudSearch"})
 */
abstract class ProductBrands extends \XLite\Model\Repo\Product implements \XLite\Base\IDecorator
{
    /**
     * Convert CommonCell conditions to CloudSearch request parameters
     *
     * @param CommonCell $cnd
     *
     * @return array
     */
    protected function getCloudSearchRequestParams(CommonCell $cnd)
    {
        $searchParams = parent::getCloudSearchRequestParams($cnd);

        if ($cnd->{self::P_BRAND_ID}) {
            $brandAttribute = Database::getRepo('XLite\Model\Attribute')
                ->findBrandAttribute();

            if ($brandAttribute) {
                $brand = Database::getRepo('XLite\Module\QSL\ShopByBrand\Model\Brand')
                    ->find($cnd->{self::P_BRAND_ID});

                if ($brand) {
                    $searchParams += [
                        'brand'          => $brand->getName(),
                        'brandAttribute' => $brandAttribute->getName(),
                    ];
                }
            }
        }

        return $searchParams;
    }

    /**
     * Prepare certain search condition
     *
     * @param QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed        $value        Condition data
     *
     * @return void
     */
    protected function prepareCndBrandId(QueryBuilder $queryBuilder, $value)
    {
        if (!$this->loadProductsWithCloudSearch()) {
            parent::prepareCndBrandId($queryBuilder, $value);
        }
    }
}