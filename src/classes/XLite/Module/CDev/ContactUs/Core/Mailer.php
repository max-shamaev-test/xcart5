<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\ContactUs\Core;

/**
 * Mailer
 */
abstract class Mailer extends \XLite\Core\Mailer implements \XLite\Base\IDecorator
{
    /**
     * New mail type
     */
    const TYPE_CONTACT_US = 'ContactUs';

    /**
     * @deprecated 5.4
     *
     * `From` storage
     *
     * @var string
     */
    protected static $fromStorage = null;

    /**
     * @deprecated 5.4
     *
     * Make some specific preparations for "Custom Headers" for SiteAdmin email type
     *
     * @param array  $customHeaders "Custom Headers" field value
     *
     * @return array new "Custom Headers" field value
     */
    protected static function prepareCustomHeadersContactUs($customHeaders)
    {
        return $customHeaders;
    }

    /**
     * Send contact us message
     *
     * @param \XLite\Module\CDev\ContactUs\Model\Contact  $contact
     * @param string|array $email Email
     *
     * @return string | null
     */
    public static function sendContactUsMessage($contact, $email)
    {
        static::register('contact', $contact);
        static::register('hideCompanyInSubject', true);

        if (is_array($email)) {
            foreach ($email as $mail) {
                static::compose(
                    static::TYPE_CONTACT_US,
                    static::composeAdminReplyTo(
                        static::getSiteAdministratorMail(),
                        [[
                            'address' => $contact->getEmail(),
                            'name'    => $contact->getName(),
                        ]]
                    ),
                    $mail,
                    'modules/CDev/ContactUs/message',
                    [],
                    true,
                    \XLite::ADMIN_INTERFACE
                );
            }
        } else {
            static::compose(
                static::TYPE_CONTACT_US,
                static::composeAdminReplyTo(
                    static::getSiteAdministratorMail(),
                    [[
                        'address' => $contact->getEmail(),
                        'name'    => $contact->getName(),
                    ]]
                ),
                $email,
                'modules/CDev/ContactUs/message',
                [],
                true,
                \XLite::ADMIN_INTERFACE
            );
        }

        return static::getMailer()->getLastError();
    }

    /**
     * Returns variables names
     *
     * @return array
     */
    protected function getVariables()
    {
        return array_merge(
            parent::getVariables(),
            [
                'contact_us_subject',
            ]
        );
    }

    /**
     * Return contact us subject
     *
     * @param string $name Variable name
     *
     * @return string
     */
    protected function getVariableValueContactUsSubject($name)
    {
        $contact = $this->getContact();

        $subject = '';
        if ($contact) {
            $subject = $contact->getSubject();
        }

        return $subject;
    }

    /**
     * Returns contact object
     *
     * @return null|\XLite\Module\CDev\ContactUs\Model\Contact
     */
    protected function getContact()
    {
        $contact = static::getMailer()->get('contact');

        return (is_object($contact) && $contact instanceof \XLite\Module\CDev\ContactUs\Model\Contact)
            ? $contact
            : null;
    }
}
