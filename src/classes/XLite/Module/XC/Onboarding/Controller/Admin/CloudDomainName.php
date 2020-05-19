<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Onboarding\Controller\Admin;

use XLite\Module\XC\Onboarding\Core\Mail\ChangeCloudDomain;

/**
 * Domain name page controller
 */
class CloudDomainName extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Domain name');
    }

    protected function doActionChangeDomain()
    {
        $domainName = \XLite\Core\Request::getInstance()->domain_name;

        if ($domainName) {
            $mail = new ChangeCloudDomain($domainName);
            if ($mail->send()) {
                \XLite\Core\TopMessage::addInfo('Your request has been sent, our manager will contact you shortly.');
            } else {
                \XLite\Core\TopMessage::addError('Something went wrong, please try again or contact us at X', ['email' => ChangeCloudDomain::HELPDESK_EMAIL]);
            }
        }
    }

    public function checkAccess()
    {
        return parent::checkAccess()
            && \XLite::getInstance()->getOptions(['service', 'is_cloud'])
            && !\XLite::getInstance()->getOptions(['service', 'is_trial']);
    }
}
