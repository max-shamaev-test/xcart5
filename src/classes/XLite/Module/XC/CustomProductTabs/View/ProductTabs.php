<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\CustomProductTabs\View;

/**
 * Product tabs page view
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class ProductTabs extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('product_tabs'));
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/CustomProductTabs/product_tabs/body.twig';
    }

    /**
     * Check - search box is visible or not
     *
     * @return boolean
     */
    protected function isSearchVisible()
    {
        return 0 < \XLite\Core\Database::getRepo('XLite\Module\XC\CustomProductTabs\Model\Product\Tab')->count();
    }

}