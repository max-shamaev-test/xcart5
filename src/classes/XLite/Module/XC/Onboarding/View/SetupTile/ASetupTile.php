<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Onboarding\View\SetupTile;


use XLite\Core\Converter;
use XLite\Core\Request;
use XLite\Core\TmpVars;

/**
 * ASetupTile
 */
abstract class ASetupTile extends \XLite\View\AView
{
    abstract protected function getContentText();
    abstract protected function getImage();
    abstract protected function getButtonLabel();
    abstract protected function getButtonURL();
    abstract protected function getButtonConciergeLinkTitle();
    abstract protected function getCloseConciergeLinkTitle();

    protected function getHeader()
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getClass()
    {
        return 'onboarding-setup-tile';
    }

    /**
     * @return string
     */
    protected function getTileType()
    {
        $class = explode('\\', get_called_class());
        $index = end($class);

        return Converter::convertFromCamelCase($index);
    }

    /**
     * @return array
     */
    protected function getTagAttributes()
    {
        return [
            'class'          => $this->getClass(),
            'data-tile-type' => $this->getTileType(),
        ];
    }

    /**
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/Onboarding/setup_tiles/tile.twig';
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        return parent::isVisible() && !$this->isClosed();
    }

    /**
     * Return update timestamp
     *
     * @return integer
     */
    protected function isClosed()
    {
        $result = TmpVars::getInstance()->{$this->getTileType() . '_tileClosed'};

        if (!$result && Request::getInstance()->{$this->getTileType() . '_tileClosed'}) {
            TmpVars::getInstance()->{$this->getTileType() . '_tileClosed'} = 1;
            $result = 1;
        }

        return $result;
    }
}