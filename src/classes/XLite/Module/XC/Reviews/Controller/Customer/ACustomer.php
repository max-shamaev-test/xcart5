<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\Reviews\Controller\Customer;

/**
 * Review modify controller
 *
 */
abstract class ACustomer extends \XLite\Controller\Customer\ACustomer implements \XLite\Base\IDecorator
{
    /**
     * Current product cache
     *
     * @var \XLite\Model\Product $product
     */
    protected $product = false;

    /**
     * Get product
     *
     * @return \XLite\Model\Product
     */
    protected function getProduct()
    {
        if ($this->product === false) {
            $this->product = \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getProductId());
        }

        return $this->product;
    }

    /**
     * Return product id of the current page
     *
     * @return integer
     */
    public function getProductId()
    {
        $productId = parent::getProductId();
        if (empty($productId)) {
            $cellName = \XLite\Module\XC\Reviews\View\ItemsList\Model\Customer\Review::getSessionCellName();
            $cell = (array)\XLite\Core\Session::getInstance()->$cellName;

            $productId = isset($cell['product_id']) ? $cell['product_id'] : null;
        }

        return $productId;
    }

    /**
     * Return TRUE if customer already reviewed product
     *
     * @param \XLite\Model\Product $product
     *
     * @return boolean
     */
    public function isProductReviewedByUser($product = null)
    {
        if (null === $product) {
            $product = $this->getProduct();
        }

        $result = false;
        if (isset($product) && $this->getProfile()) {
            $result = $product->isReviewedByUser($this->getProfile());
        }

        return $result;
    }

    /**
     * Return TRUE if customer can add review for product
     *
     * @param \XLite\Model\Product $product
     *
     * @return boolean
     */
    public function isAllowedAddReview($product = null)
    {
        $result = !$this->isProductReviewedByUser($product);

        if ($result
            && $this->isPurchasedCustomerOnlyAbleLeaveFeedback()
            && !$this->isUserPurchasedProduct($product)
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * Check if add review should request user to login
     *
     * @return bool
     */
    public function isReplaceAddReviewWithLogin()
    {
        return !(boolean)$this->getProfile();
    }

    /**
     * Return message instead of 'Add review' button if customer is not allowed to add review
     *
     * @return string
     */
    public function getAddReviewMessage()
    {
        $message = null;

        if (empty($message) && $this->getProfile() && $this->isProductReviewedByUser()) {
            $message = 'You have already reviewed this product';
        }

        if (empty($message) && $this->isPurchasedCustomerOnlyAbleLeaveFeedback()) {
            $message = 'Only customers who purchased this product can leave feedback on this product';
        }

        return static::t($message);
    }

    /**
     * Return TRUE if only customers who purchased this product can leave feedback
     *
     * @return boolean
     */
    public function isPurchasedCustomerOnlyAbleLeaveFeedback()
    {
        $whoCanLeaveFeedback = \XLite\Core\Config::getInstance()->XC->Reviews->whoCanLeaveFeedback;

        return (\XLite\Module\XC\Reviews\Model\Review::PURCHASED_CUSTOMERS == $whoCanLeaveFeedback);
    }

    /**
     * Return true if customer purchased the specified product
     *
     * @param \XLite\Model\Product $product
     *
     * @return boolean
     */
    protected function isUserPurchasedProduct($product)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\OrderItem')
            ->countItemsPurchasedByCustomer($product ? $product->getId() : $this->getProductId(), $this->getProfile());
    }

    /**
     * Define if review is added by current user
     *
     * @param \XLite\Module\XC\Reviews\Model\Review $entity
     *
     * @return bool
     */
    public function isOwnReview(\XLite\Module\XC\Reviews\Model\Review $entity)
    {
        $result = false;

        $profile = \XLite\Core\Auth::getInstance()->getProfile();
        if ($profile && $entity->getProfile()) {
            $result = ($entity->getProfile()->getProfileId() === $profile->getProfileId());
        }

        return $result;
    }
}
