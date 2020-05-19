<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Onboarding\Core\Mail;

/**
 * Class ChangeCloudDomain
 * @package XLite\Module\XC\Onboarding\Core\Mail
 */
class ChangeCloudDomain extends \XLite\Core\Mail\AMail
{
    const HELPDESK_EMAIL = 'helpdesk@x-cart.com';

    static function getInterface()
    {
        return \XLite::ADMIN_INTERFACE;
    }

    static function getDir()
    {
        return 'modules/XC/Onboarding/change_cloud_domain';
    }

    public function __construct($domainName)
    {
        parent::__construct();

        $this->setFrom(null);
        $this->setTo(static::HELPDESK_EMAIL);

        $cloudAccountEmail = \XLite::getInstance()->getOptions(['service', 'cloud_account_email']);
        if ($cloudAccountEmail) {
            $this->addReplyTo($cloudAccountEmail);
        }

        $this->appendData([
            'domainName' => $domainName,
        ]);
    }

    public function isSeparateMailer()
    {
        return true;
    }

    public function prepareSeparateMailer(\XLite\View\Mailer $mailer)
    {
        $mailer = parent::prepareSeparateMailer($mailer);

        $mailer->setSubjectTemplate('modules/XC/Onboarding/change_cloud_domain/subject.twig');
        $mailer->setLayoutTemplate('modules/XC/Onboarding/change_cloud_domain/body.twig');

        return $mailer;
    }
}