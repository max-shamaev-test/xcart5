<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\CloudSearch\View;

use XLite;
use XLite\Module\QSL\CloudSearch\Main;


/**
 * Abstract widget
 */
abstract class AView extends \XLite\View\AView implements \XLite\Base\IDecorator
{
    /**
     * Get a list of JS files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        if (!XLite::isAdminZone()) {
            $list[] = 'modules/QSL/CloudSearch/init.js';
        }

        return $list;
    }

    /**
     * @return array
     */
    protected function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        if (!XLite::isAdminZone()) {
            $list[static::RESOURCE_JS][] = 'modules/QSL/CloudSearch/loader.js';
            $list[static::RESOURCE_JS][] = 'modules/QSL/CloudSearch/lib/handlebars.min.js';
            $list[static::RESOURCE_JS][] = 'modules/QSL/CloudSearch/lib/jquery.hoverIntent.min.js';

            $list[static::RESOURCE_CSS][] = 'modules/QSL/CloudSearch/style.less';
        }

        return $list;
    }

    protected function isCloudFiltersMobileLinkVisible()
    {
        return Main::isCloudFiltersEnabled()
            && in_array($this->getTarget(), [
                'category',
                'search',
                'sale_products',
                'bestsellers',
                'new_arrivals',
                'coming_soon',
                'vendor',
                'brand',
            ], true);
    }
}
