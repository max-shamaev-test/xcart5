<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/module-marketplace-terms-of-use.html for license details.
 */

namespace XLite\Module\QSL\SpecialOffersBase;

/**
 * Main module
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'Qualiteam Software';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Special Offers (base)';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'This is the base module that allows other modules to add different types of special offers.';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.3';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '6';
    }


    /**
     * Get module build number (4th number in the version)
     *
     * @return string
     */
    public static function getBuildVersion()
    {
        return '4';
    }

    /**
     * Get minor core version which is required for the module activation
     *
     * @return string
     */
    public static function getMinorRequiredCoreVersion()
    {
        return '0';
    }

    /**
     * Determines if we need to show settings form link
     *
     * @return boolean
     */
    public static function showSettingsForm()
    {
        return true;
    }

    /**
     * Actions that take place after redeploying the store.
     *
     * @return void
     */
    public static function runBuildCacheHandler()
    {
        parent::runBuildCacheHandler();

        \XLite\Core\Layout::getInstance()->removeTemplateFromList('shopping_cart/parts/item.subtotal.twig', 'cart.item');

        static::updateOfferTypes();

        if (class_exists('\XLite\Module\CDev\SimpleCMS\Model\Menu')) {
            static::addSimpleCMSMenuLink();
        }

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Updates offer types and disable those that have no enabled modules.
     *
     * @return void
     */
    protected static function updateOfferTypes()
    {
        foreach (\XLite\Core\Database::getRepo('XLite\Module\QSL\SpecialOffersBase\Model\OfferType')->findAll() as $type) {
            $enabled = $type->hasAllRequiredClasses();
            $type->setEnabled($enabled);
            if (!$enabled) {
                foreach ($type->getSpecialOffers() as $offer) {
                    $offer->setEnabled(false);
                }
            }
        }

    }

    /**
     * Adds the Special Offers entry to the primary storefront menu if it is not there yet.
     *
     * @return void
     */
    protected static function addSimpleCMSMenuLink()
    {
        $repo = \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\Menu');
        $repoLang = \XLite\Core\Database::getRepo('XLite\Module\CDev\SimpleCMS\Model\MenuTranslation');

        $link = \XLite\Module\QSL\SpecialOffersBase\Model\Menu::DEFAULT_OFFERS_PAGE;

        $item = $repo->findOneByLink('?target=special_offers');
        if ($item) {
            $item->setLink($link);
        } else {

            $item = $repo->findOneByLink($link);
            if (!$item) {

                $item = new \XLite\Module\CDev\SimpleCMS\Model\Menu(
                    array(
                        'enabled'  => false,
                        'link'     => $link,
                        'type'     => \XLite\Module\CDev\SimpleCMS\Model\Menu::MENU_TYPE_PRIMARY,
                        'position' => 150,
                    )
                );
                $repo->insert($item);

                $en = new \XLite\Module\CDev\SimpleCMS\Model\MenuTranslation(
                    array(
                        'code' => 'en',
                        'name' => 'Special offers',
                    )
                );
                $en->setOwner($item);
                $item->addTranslations($en);
                $repoLang->insert($en);

                $ru = new \XLite\Module\CDev\SimpleCMS\Model\MenuTranslation(
                    array(
                        'code' => 'ru',
                        'name' => 'Акции',
                    )
                );
                $ru->setOwner($item);
                $item->addTranslations($ru);
                $repoLang->insert($ru);
            }
        }
    }
}
