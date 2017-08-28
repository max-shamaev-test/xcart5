<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Model\Order\Status;

/**
 * Shipping status
 *
 * @Entity
 * @Table  (name="order_shipping_statuses",
 *      indexes={
 *          @Index (name="code", columns={"code"})
 *      }
 * )
 */
class Shipping extends \XLite\Model\Order\Status\AStatus
{
    /**
     * Statuses
     */
    const STATUS_NEW                 = 'N';
    const STATUS_PROCESSING          = 'P';
    const STATUS_SHIPPED             = 'S';
    const STATUS_DELIVERED           = 'D';
    const STATUS_WILL_NOT_DELIVER    = 'WND';
    const STATUS_RETURNED            = 'R';
    const STATUS_WAITING_FOR_APPROVE = 'WFA';

    /**
     * Disallowed to set manually statuses
     *
     * @return array
     */
    public static function getDisallowedToSetManuallyStatuses()
    {
        return [
            self::STATUS_WAITING_FOR_APPROVE,
        ];
    }

    /**
     * Status is allowed to set manually
     *
     * @return boolean
     */
    public function isAllowedToSetManually()
    {
        return !in_array(
            $this->getCode(),
            static::getDisallowedToSetManuallyStatuses()
        );
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Shipping
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Shipping
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * List of change status handlers;
     * top index - old status, second index - new one
     * (<old_status> ----> <new_status>: $statusHandlers[$old][$new])
     *
     * @return array
     */
    public static function getStatusHandlers()
    {
        return [
            self::STATUS_NEW => [
                self::STATUS_SHIPPED => ['ship'],
                self::STATUS_WAITING_FOR_APPROVE => ['waitingForApprove']
            ],

            self::STATUS_PROCESSING => [
                self::STATUS_SHIPPED => ['ship'],
                self::STATUS_WAITING_FOR_APPROVE => ['waitingForApprove']
            ],

            self::STATUS_DELIVERED => [
                self::STATUS_SHIPPED => ['ship'],
            ],

            self::STATUS_WILL_NOT_DELIVER => [
                self::STATUS_SHIPPED => ['ship'],
            ],

            self::STATUS_RETURNED => [
                self::STATUS_SHIPPED => ['ship'],
            ],

            self::STATUS_WAITING_FOR_APPROVE => [
                self::STATUS_SHIPPED => ['ship'],
            ],
        ];
    }
}
