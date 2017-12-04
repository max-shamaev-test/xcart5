<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\Paypal\View\Button;

/**
 * Express Checkout base button
 */
abstract class AExpressCheckout extends \XLite\View\Button\Link
{
    const PARAM_IN_CONTEXT = 'inContext';

    /**
     * Returns true if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $cart = $this->getCart();

        return parent::isVisible() && \XLite\Module\CDev\Paypal\Main::isExpressCheckoutEnabled($cart);
    }

    /**
     * defineWidgetParams
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams[static::PARAM_LOCATION] = new \XLite\Model\WidgetParam\TypeString(
            'Redirect to',
            $this->buildURL('checkout', 'start_express_checkout')
        );
    }

    protected function getDefaultTemplate()
    {
        return 'modules/CDev/Paypal/button/ec_button.twig';
    }

    public function getJSFiles()
    {
        return array_merge(parent::getJSFiles(), [
            'modules/CDev/Paypal/checkout.js',
            'modules/CDev/Paypal/button/js/button.js',
        ]);
    }

    /**
     * @return string
     */
    protected function getButtonClass()
    {
        return 'pp-express-checkout-button';
    }

    protected function getAllowedLocales()
    {
        return [
            'en_US',
            'en_AU',
            'en_GB',
            'fr_CA',
            'es_ES',
            'it_IT',
            'fr_FR',
            'de_DE',
            'pt_BR',
            'zh_CN',
            'da_DK',
            'zh_HK',
            'id_ID',
            'he_IL',
            'ja_JP',
            'nl_NL',
            'no_NO',
            'pl_PL',
            'pt_PT',
            'ru_RU',
            'sv_SE',
            'th_TH',
            'zh_TW',
        ];
    }

    protected function getLocale()
    {
        $locale = @mb_substr(\XLite\Core\Converter::getLocaleByCode(), 0, 5);

        return in_array($locale, $this->getAllowedLocales())
            ? $locale
            : null;
    }

    protected function getButtonAdditionalParams()
    {
        $result = [];

        if ($this->getLocale()) {
            $result['data-locale'] = $this->getLocale();
        }

        return $result;
    }

    protected function getButtonAdditionalParamsCode()
    {
        return implode(' ', array_map(function ($k, $v) {
            return "{$k}={$v}";
        }, array_keys($this->getButtonAdditionalParams()), $this->getButtonAdditionalParams()));
    }
}
