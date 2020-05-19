<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Onboarding\View;

use XLite\Module\XC\Onboarding\Main;

/**
 * Domain name page
 * @ListChild (list="admin.center", zone="admin")
 */
class DomainNamePage extends \XLite\View\AView
{
    /**
     * @return array
     */
    public static function getAllowedTargets()
    {
        return [
            'cloud_domain_name'
        ];
    }

    /**
     * @return array
     */
    public function getCSSFiles()
    {
        return [
            $this->getDir() . '/style.less',
        ];
    }

    /**
     * @return array
     */
    public function getJSFiles()
    {
        return [
            $this->getDir() . '/controller.js',
        ];
    }

    protected function getDomainName()
    {
        return Main::getCloudDomainName();
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/Onboarding/domain_name_page';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.twig';
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        return \XLite::getInstance()->getOptions(['service', 'is_cloud'])
            && !\XLite::getInstance()->getOptions(['service', 'is_trial']);
    }
}