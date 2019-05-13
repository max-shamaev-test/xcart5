<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\FacebookMarketing\View\Header;

/**
 * Facebook pixel header
 *
 * @ListChild (list="head", zone="customer")
 */
class Pixel extends \XLite\View\AView
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/FacebookMarketing/header/pixel.twig';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && \XLite\Module\XC\FacebookMarketing\Main::isPixelEnabled();
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function getPixelId()
    {
        return \XLite\Core\Config::getInstance()->XC->FacebookMarketing->pixel_id;
    }
}