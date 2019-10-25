<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\NextPreviousProduct\View;

/**
 * Initialization
 *
 * @ListChild (list="head", zone="customer", weight="1305")
 */
class Initialization extends \XLite\View\AView
{
    /**
     * Get a list of JavaScript files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list   = parent::getJSFiles();
        $list[] = 'modules/XC/NextPreviousProduct/head.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/NextPreviousProduct/head.twig';
    }

    /**
     * Get cookie path
     *
     * @return string
     */
    protected function getCookiePath()
    {
        $path = \XLite::getInstance()->getOptions(array('host_details', 'web_dir')) ?: '/';

        return $path;
    }
}
