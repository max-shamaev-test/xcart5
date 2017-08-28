<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\View\Button\Features;

/**
 * Tooltipped button trait
 */
trait TooltippedTrait
{
    /**
     * @return string
     */
    protected static function getTooltipWidgetParamName()
    {
        return 'titleAsTooltip';
    }

    /**
     * @return string
     */
    protected function isTitleShownAsTooltip()
    {
        return $this->getParam(static::getTooltipWidgetParamName()) === null
            ? true
            : $this->getParam(static::getTooltipWidgetParamName());
    }

    /**
     * Defines the button specific attributes
     *
     * @return array
     */
    protected function getButtonAttributes()
    {
        $list = parent::getButtonAttributes();

        if ($this->isTitleShownAsTooltip()) {
            $list['data-toggle'] = 'tooltip';
            $list['data-placement'] = 'top';
            $list['data-container'] = 'body';
        }

        return $list;
    }
}
