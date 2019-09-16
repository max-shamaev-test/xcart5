<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\SimpleCMS\View\FormField\Input;

/**
 * Logo
 */
class Logo extends \XLite\Module\CDev\SimpleCMS\View\FormField\Input\AImage
{
    /**
     * @return boolean
     */
    protected function hasAlt()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getFieldLabelTemplate()
    {
        return 'form_field/label/logo_label.twig';
    }
}