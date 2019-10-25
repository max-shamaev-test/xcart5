<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XPay\XPaymentsCloud\Controller\Customer;

/**
 * X-Payments Saved cards 
 */
class XpaymentsCards extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Check - controller must work in secure zone or not
     *
     * @return boolean
     */
    public function isSecure()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return static::t('Saved cards');
    }

    /**
     * Check whether the title is to be displayed in the content area
     *
     * @return boolean
     */
    public function isTitleVisible()
    {
        return \XLite\Core\Request::getInstance()->widget && $this->checkAccess();
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess() && \XLite\Core\Auth::getInstance()->isLogged();
    }

    /**
     * Define current location for breadcrumbs
     *
     * @return string
     */
    protected function getLocation()
    {
        return $this->getTitle();
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        $this->addLocationNode('My account');
    }

    /**
     * Get customer profile (wrapper)
     *
     * @return \XLite\Model\Profile
     */
    protected function getCustomerProfile()
    {
        return $this->getProfile();
    }

    /**
     * Remove X-Payments saved card
     *
     * @return void
     */
    protected function doActionRemove()
    {
        $profile = $this->getCustomerProfile();

        $cardId = \XLite\Core\Request::getInstance()->card_id;

        if ($profile->removeXpaymentsCard($cardId)) {
            \XLite\Core\TopMessage::addInfo('Saved card has been deleted');
        } else {
            \XLite\Core\TopMessage::addError('Failed to delete saved card');
        }

        $this->setHardRedirect();
        $this->setReturnURL($this->buildURL('xpayments_cards'));
        $this->doRedirect();
    }
}
