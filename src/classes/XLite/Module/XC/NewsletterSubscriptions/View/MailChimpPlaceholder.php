<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\NewsletterSubscriptions\View;

use Includes\Utils\Module\Manager;
use XLite\Module\XC\MailChimp\Core;

/**
 * Placeholder
 *
 * @ListChild (list="admin.center", zone="admin", weight="0")
 */
class MailChimpPlaceholder extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $return = parent::getAllowedTargets();
        $return[] = 'newsletter_subscribers';

        return $return;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $return = parent::getCSSFiles();
        $return[] = 'main/style.css';

        return $return;
    }

    /**
     * Get logo url
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return \XLite\Core\Layout::getInstance()->getResourceWebPath(
            'modules/XC/NewsletterSubscriptions/mail_chimp/logo.png'
        );
    }

    /**
     * Check if XC\MailChimp configured
     *
     * @return boolean
     */
    public function isMailChimpConfigured()
    {
        return $this->isMailChimpInstalled()
            && Core\MailChimp::hasAPIKey();
    }

    /**
     * Check if XC\MailChimp configured
     *
     * @return boolean
     */
    public function isMailChimpInstalled()
    {
        return Manager::getRegistry()->isModuleEnabled('XC', 'MailChimp');
    }

    /**
     * MailChimp external sign in url
     *
     * @return string
     */
    public function getMailChimpSignInLink()
    {
        return 'https://login.mailchimp.com/signup?source=website&pid=xcart';
    }

    /**
     * MailChimp setting url
     *
     * @return string
     */
    public function getMailChimpSettingsLink()
    {
        return \XLite\Module\XC\MailChimp\Main::getSettingsForm();
    }

    /**
     * Check if there is subscribers already in database
     *
     * @return boolean
     */
    public function isItemsListEmpty()
    {
        return !\XLite\Core\Database::getRepo('XLite\Module\XC\NewsletterSubscriptions\Model\Subscriber')
            ->count();
    }

    /**
     * Export subscribers link with preselected options
     *
     * @return string
     */
    public function getExportSubscribersLink()
    {
        $subscribersStepName = 'XLite\Module\XC\NewsletterSubscriptions\Logic\Export\Step\NewsletterSubscribers';

        return $this->buildURL(
            'export',
            '',
            [
                \XLite\View\Export\Begin::PARAM_PRESELECT => $subscribersStepName,
            ]
        );
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/NewsletterSubscriptions/mail_chimp/items_list_header.twig';
    }

    /**
     * Get recommended module URL
     *
     * @return string
     */
    protected function getAddonLink()
    {
        return Manager::getRegistry()->isModuleEnabled('XC', 'MailChimp')
            ? null
            : Manager::getRegistry()->getModuleServiceURL('XC', 'MailChimp');
    }
}
