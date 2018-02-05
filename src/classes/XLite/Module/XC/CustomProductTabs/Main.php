<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\CustomProductTabs;

/**
 * Main module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'X-Cart team';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Custom product tabs';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Add additional tabs on the product page. Custom name, custom content - html text or product videos, whatever describes your product best.';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.3';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '4';
    }

    /**
     * Get module build number (4th number in the version)
     *
     * @return string
     */
    public static function getBuildVersion()
    {
        return '1';
    }

    /**
     * Get minor core version which is required for the module activation
     *
     * @return string
     */
    public static function getMinorRequiredCoreVersion()
    {
        return '4';
    }

    /**
     * Decorator run this method at the end of cache rebuild
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        $qb = \XLite\Core\Database::getRepo('XLite\Model\Product\GlobalTab')->createQueryBuilder();

        $alias = $qb->getMainAlias();
        $qb->addSelect('COUNT(psa.id) as HIDDEN aliases_count')
            ->leftJoin("{$alias}.product_specific_aliases", 'psa')
            ->andWhere("{$alias}.service_name IS NOT NULL")
            ->having('aliases_count < :products_count')
            ->groupBy("{$alias}.id")
            ->setParameter('products_count', \XLite\Core\Database::getRepo('XLite\Model\Product')->count());

        foreach ($qb->getResult() as $globalTab) {
            \XLite\Core\Database::getRepo('XLite\Model\Product\GlobalTab')->createGlobalTabAliases($globalTab);
        }

    }
}
