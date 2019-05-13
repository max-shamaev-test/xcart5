<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\MailChimp\Model;

use XLite\Core\Request;
use \XLite\Module\XC\MailChimp\Core;
use XLite\Module\XC\MailChimp\Core\MailChimpQueue;
use XLite\Module\XC\MailChimp\Main;

/**
 * Class represents an order
 */
abstract class Cart extends \XLite\Model\Cart implements \XLite\Base\IDecorator
{
    protected static $mcNewCartFlag;

    protected function needUpdateMailchimpCart()
    {
        $request = \XLite\Core\Request::getInstance();
        $result = true;

        if (
            $request->widget == '\XLite\View\Minicart'
            || (
                $request->target == 'checkout'
                && $request->action !== 'shipping'
            )
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * Prepare order before save data operation
     *
     * @PrePersist
     * @PreUpdate
     */
    public function prepareBeforeSave()
    {
        parent::prepareBeforeSave();

        if ($this->needUpdateMailchimpCart()
            && Core\MailChimp::hasAPIKey()
            && Main::isMailChimpAbandonedCartEnabled()
            && !$this->getOrderNumber()
            && $this->getProfile()
            && $this->getProfile()->getEmail()
        ) {
            MailChimpQueue::getInstance()->addAction(
                'cartUpdate',
                new Core\Action\CartUpdate($this)
            );
        }
    }

    /**
     * Method to access a singleton
     *
     * @param boolean $doCalculate Flag for cart recalculation OPTIONAL
     *
     * @return \XLite\Model\Cart
     */
    public static function getInstance($doCalculate = true)
    {
        $cart = parent::getInstance($doCalculate);

        if (!isset(static::$mcNewCartFlag)) {
            static::$mcNewCartFlag = !$cart->isPersistent();
        }

        return $cart;
    }

    /**
     * Check if mc should create new cart without checking
     *
     * @return boolean
     */
    public static function isMcNewCart()
    {
        return static::$mcNewCartFlag;
    }

    /**
     * Called when an order successfully placed by a client
     *
     * @return void
     */
    public function processSucceed()
    {
        parent::processSucceed();

        if (
            (
                $this->isECommerce360Order()
                || Main::getStoreForDefaultAutomation()
            )
            && Core\MailChimp::hasAPIKey()
            && Main::isMailChimpECommerceConfigured()
        ) {
            try {
                $mcCore = Core\MailChimp::getInstance();
                $result = $mcCore->createOrder($this);
                $storeId = $mcCore->getStoreIdByCampaign(
                    Request::getInstance()->{Request::MAILCHIMP_CAMPAIGN_ID}
                );
                $this->setMailchimpStoreId($storeId);

                if ($result) {
                    Core\MailChimp::getInstance()->removeCart($this);
                }
            } catch (\Exception $e) {
                \XLite\Logger::getInstance()->log($e->getMessage());
            }
        }

        $profile = $this->getAvailableProfile();

        if (
            isset($profile)
            && $profile->hasMailChimpSubscriptions()
        ) {
            $profile->checkSegmentsConditions();
        }
    }

    /**
     * Get available profile
     *
     * @return \XLite\Model\Profile
     */
    protected function getAvailableProfile()
    {
        return $this->getOrigProfile() ? $this->getOrigProfile() : $this->getProfile();
    }
}
