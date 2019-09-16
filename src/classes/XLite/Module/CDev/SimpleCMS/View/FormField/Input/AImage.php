<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\SimpleCMS\View\FormField\Input;

/**
 * Image
 *
 */
abstract class AImage extends \XLite\View\FormField\Input\AInput
{
    /**
     * Return field type
     *
     * @return string
     */
    public function getFieldType()
    {
        return 'file';
    }

    /**
     * Return the image URL value
     *
     * @return string
     */
     protected function getImage() {
         $func = 'get' . $this->getClassName();
         $image = $this->$func();

         return \XLite\Model\Repo\Image\Common\Logo::getFakeImageObject($image);
     }


    /**
     * Return the field name for widget.
     *
     * @return string
     */
    protected function getImageName()
    {
        return lcfirst($this->getClassName());
    }

    /**
     * @return boolean
     */
    protected function isRemovable()
    {
        return \XLite\Core\Config::getInstance()->CDev->SimpleCMS->{lcfirst($this->getClassName())}
            ? true
            : false;
    }

    /**
     * @return boolean
     */
    protected function showDimensionsLink()
    {
        return false;
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return '/form_field/image.twig';
    }

    /**
     * getDir
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/CDev/SimpleCMS';
    }

    /**
     * @return boolean
     */
    protected function hasAlt()
    {
        return false;
    }

    /**
     * Return class name
     *
     * @return string
     */
    private function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
