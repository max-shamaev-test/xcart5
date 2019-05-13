<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\Module\XC\MultiVendor\View\ItemsList\Model\Order\Admin;

/**
 * Search order
 *
 * @Decorator\Depend({"CDev\XPaymentsConnector","XC\MultiVendor"})
 */
class Search extends \XLite\View\ItemsList\Model\Order\Admin\Search implements \XLite\Base\IDecorator
{
    /**
     * Get column value
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model
     *
     * @return mixed
     */
    protected function getColumnClass(array $column, \XLite\Model\AEntity $entity = null)
    {
        $entity = $this->getParentOrChildEntity($entity);

        $result = parent::getColumnClass($column, $entity);
       
        if ('fraud_status_xpc' == $column[static::COLUMN_CODE]) {
            $result = 'fraud-status-' . $entity->getFraudStatusXpc();
        }

        return $result;
    }

    /**
     * Build entity page URL
     *
     * @param \XLite\Model\AEntity $entity Entity
     * @param array                $column Column data
     *
     * @return string
     */
    protected function getFraudInfoXpcLink(\XLite\Model\AEntity $entity)
    {
        $result = \XLite\Core\Converter::buildURL(
            'order',
            '',
            array('order_number' => $entity->getOrderNumber())
        );

        $result .= '#' . $this->getParentOrChildEntity($entity)->getFraudInfoXpcAnchor();

        return $result;
    }

    /**
     * Get column value
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model
     *
     * @return mixed
     */
    protected function getFraudInfoXpcTitle(\XLite\Model\AEntity $entity)
    {
        return $this->getParentOrChildEntity($entity)->getFraudStatusXpc();
    }

    /**
     * Return parent entity if it has parent, or entity itself otherwise
     *
     * @param \XLite\Model\AEntity $entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function getParentOrChildEntity(\XLite\Model\AEntity $entity)
    {
        if ($entity->isChild()) {
            $entity = $entity->getParent();
        }

        return $entity;
    }
}