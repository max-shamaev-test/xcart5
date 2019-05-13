<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\XPaymentsConnector\Model\Repo\Payment;

/**
 * Payment method repository
 */
class Method extends \XLite\Model\Repo\Payment\Method implements \XLite\Base\IDecorator
{
    /**
     * Names of fields that are used in search
     */
    const P_CLASS = 'class';
    const P_FROM_MARKETPLACE = 'fromMarketplace';

    /**
     * Prepare certain search condition for enabled flag
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag
     *
     * @return void
     */
    protected function prepareCndClass(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $queryBuilder->andWhere($this->getMainAlias($queryBuilder) . '.class = :class_value')
            ->setParameter('class_value', $value);
    }

    /**
     * Prepare certain search condition for enabled flag
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param boolean                    $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag
     *
     * @return void
     */
    protected function prepareCndFromMarketplace(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $queryBuilder->andWhere($this->getMainAlias($queryBuilder) . '.fromMarketplace = :fromMarketplaceValue')
            ->setParameter('fromMarketplaceValue', $value);
    }
}
