<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\FacebookMarketing\View;

/**
 * Google feed promo banner
 */
class GoogleFeedBanner extends \XLite\View\AView
{
    use \XLite\Core\Cache\ExecuteCachedTrait;

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/XC/FacebookMarketing/google_feed_banner/style.less';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/FacebookMarketing/google_feed_banner/body.twig';
    }

    /**
     * @return string
     */
    protected function getGoogleFeedPromoText()
    {
        return static::t('Use the module Google Product Feed for advanced flexibility generating a data feed for Facebook based on the product attributes and variants from your store catalog');
    }

    /**
     * @return \XLite\Model\Module
     */
    protected function getGoogleFeedModule()
    {
        return $this->executeCachedRuntime(function () {
            $module = \XLite\Core\Database::getRepo('XLite\Model\Module')->findOneBy(
                [
                    'author' => 'XC',
                    'name'   => 'GoogleFeed',
                ],
                [ 'fromMarketplace' => 'ASC' ]
            );

            return $module;
        });
    }

    /**
     * @return bool
     */
    protected function isGoogleFeedEnabled()
    {
        $module = $this->getGoogleFeedModule();

        return $module && $module->getEnabled();
    }

    /**
     * @return string
     */
    protected function getGoogleFeedButtonLabel()
    {
        return $this->isGoogleFeedEnabled() ? static::t('Configure') : static::t('Install');
    }

    /**
     * @return string
     */
    protected function getGoogleFeedModuleLink()
    {
        if ($this->isGoogleFeedEnabled()) {
            $link = $this->buildURL('google_shopping_groups');
        } else {
            $module = $this->getGoogleFeedModule();

            $link = $module->getFromMarketplace()
                ? $module->getMarketplaceURL()
                : $module->getInstalledURL();
        }

        return $link;
    }

    /**
     * @return string
     */
    protected function getGoogleFeedLogoUrl()
    {
        $module = $this->getGoogleFeedModule();

        return $module ? $module->getPublicIconURL() : null;
    }
}