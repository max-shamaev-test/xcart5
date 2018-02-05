<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2016-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/module-marketplace-terms-of-use.html for license details.
 */

namespace XLite\Module\QSL\SpecialOffersBase\View\ItemsList\Model;

use XLite\Core\Database;

/**
 * Promoted special offer modules.
 */
class PromotedModules extends \XLite\View\AView
{
    protected $modules;

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/QSL/SpecialOffersBase/special_offers/special_offer_modules.twig';
    }

    /**
     * Returns an array of special offer related modules.
     *
     * @return array
     */
    protected function getPromotedModules()
    {
        if (!isset($this->modules)) {
            $repo = Database::getRepo('XLite\Model\Module');

            $this->modules = [
                'bxgy' => [
                    'author' => 'QSL',
                    'code' => 'SpecialOffersBuyXGetY',
                    'cssClass' => 'special-offer-mod--buy-x-get-y',
                ],
                'sxgy' => [
                    'author' => 'QSL',
                    'code' => 'SpecialOffersSpendXGetY',
                    'cssClass' => 'special-offer-mod--spend-x-get-y',
                ],
                'roulette' => [
                    'author' => 'QSL',
                    'code' => 'Roulette',
                    'cssClass' => 'special-offer-mod--roulette',
                ],
                'loyalty' => [
                    'author' => 'QSL',
                    'code' => 'LoyaltyProgram',
                    'cssClass' => 'special-offer-mod--loyalty',
                ],
                'popups' => [
                    'author' => 'QSL',
                    'code' => 'PopupAnywhere',
                    'cssClass' => 'special-offer-mod--popups',
                ],
            ];

            foreach ($this->modules as $k=>$m) {
                $module = $repo->findOneBy(
                    [
                        'author' => $m['author'],
                        'name' => $m['code']
                    ],
                    [
                        'fromMarketplace' => 'ASC'
                    ]
                );
                if ($module) {
                    $this->modules[$k]['name'] = $module->getName();
                    $this->modules[$k]['url'] = $module->getMarketplaceURL();
                } else {
                    unset($this->modules[$k]);
                }
            }
        }

        return $this->modules;
    }

    function getPromotedModuleInfo($moduleName, $fieldName)
    {
        $info = $this->getPromotedModules();

        return $info[$moduleName][$fieldName];
    }

    function getModulesPromoLink()
    {
        return $this->buildUrl(
            'addons_list_marketplace',
            '',
            ['substring' => 'special offers']
        );
    }

    function getScreenshotPath()
    {
        return \XLite::getInstance()->getShopURL(
            'skins/admin/modules/QSL/SpecialOffersBase/special_offers/promo'
        );
    }
}