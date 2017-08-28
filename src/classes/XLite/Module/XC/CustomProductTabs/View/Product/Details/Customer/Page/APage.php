<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\CustomProductTabs\View\Product\Details\Customer\Page;

/**
 * APage
 */
class APage extends \XLite\View\Product\Details\Customer\Page\APage implements \XLite\Base\IDecorator
{
    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'modules/XC/CustomProductTabs/product/controller.js';

        return $list;
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/XC/CustomProductTabs/product/style.css';

        return $list;
    }

    /**
     * Define tabs
     *
     * @return array
     */
    protected function defineTabs()
    {
        $list = parent::defineTabs();

        foreach ($this->getProduct()->getTabs() as $tab) {
            $this->processTab($list, $tab);
        }

        return $list;
    }

    /**
     * Process tab addition into list
     *
     * @param                                                      $list
     * @param \XLite\Module\XC\CustomProductTabs\Model\Product\Tab $tab
     */
    protected function processTab(&$list, $tab)
    {
        if ($tab->isAvailable()) {
            if ($tab->isGlobalStatic()) {
                $this->processGlobalTab($list, $tab);
            } else {
                $list['tab' . $tab->getId()] = [
                    'widget'     => '\XLite\Module\XC\CustomProductTabs\View\Product\Tabs\Tab',
                    'parameters' => [
                        'tab' => $tab,
                    ],
                    'name'       => $tab->getName(),
                    'weight'     => $tab->getPosition(),
                ];
            }
        }
    }

    /**
     * Returns list of tabs brief info [tab_link => info]
     *
     * @return array
     */
    public function getTabsBriefInfo()
    {
        $result = [];

        foreach ($this->getProduct()->getTabs() as $tab) {
            if ($tab->getEnabled() && $tab->getBriefInfo()) {
                $result['product-details-tab-' . preg_replace('/\W+/Ss', '-', strtolower('tab' . $tab->getId()))] = [
                    'brief_info' => $tab->getBriefInfo(),
                    'title' => $tab->getName()
                ];
            }
        }

        return $result;
    }

    /**
     * Returns list of tabs brief info [tab_link => info]
     *
     * @return bool
     */
    public function hasTabsBriefInfo()
    {
        return count($this->getTabsBriefInfo()) > 0;
    }
}
