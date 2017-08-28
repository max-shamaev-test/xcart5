<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\VendorMessages\Model\Repo;


/**
 * Conversation Repository
 */
class Conversation extends \XLite\Model\Repo\ARepo
{
    const SEARCH_MESSAGES          = 'messages';
    const SEARCH_MESSAGE_SUBSTRING = 'messageSubstring';

    const P_MEMBER            = 'member';
    const P_ORDERS_ONLY       = 'ordersOnly';
    const P_ORDER_BY          = 'orderBy';
    const P_ORDERS_CONDITIONS = 'ordersConditions';

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param \XLite\Model\Profile       $value        Condition data
     *
     * @return void
     */
    protected function prepareCndMember(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value) {
            $alias = $this->getMainAlias($queryBuilder);
            $queryBuilder->andWhere(":member MEMBER OF {$alias}.members")
                ->setParameter('member', $value);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param \XLite\Model\Profile       $value        Condition data
     *
     * @return void
     */
    protected function prepareCndOrdersOnly(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value) {
            $alias = $this->getMainAlias($queryBuilder);
            $queryBuilder->linkInner("{$alias}.order", 'o');
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder Query builder to prepare
     * @param integer                                 $value        Condition data
     *
     * @return void
     */
    protected function prepareCndMessages(\XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $alias = $this->getMainAlias($queryBuilder);
            switch ($value) {
                case 'U':
                    $queryBuilder->linkInner("{$alias}.messages")
                        ->linkLeft('messages.readers', 'r0', \Doctrine\ORM\Query\Expr\Join::WITH, 'r0.reader = :reader')
                        ->linkLeft('messages.readers', 'r1')
                        ->andHaving('COUNT(r1.id) != SUM(IF(r0.id IS NULL, 0, 1)) OR COUNT(r1.id) = 0')
                        ->setParameter('reader', \XLite\Core\Auth::getInstance()->getProfile());
                    break;

                case 'A':
                    $queryBuilder->linkInner("{$alias}.messages");
                    break;

                case 'D':
                    $queryBuilder->linkInner("{$alias}.order", 'o');
                    if (\XLite\Module\XC\VendorMessages\Main::isAllowDisputes()) {
                        $queryBuilder->andWhere("o.is_opened_dispute = :order_dispute_state")
                            ->setParameter('order_dispute_state', true);
                    }
                    break;
            }
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder Query builder to prepare
     * @param string                                  $value        Condition data
     */
    protected function prepareCndMessageSubstring(\XLite\Model\QueryBuilder\AQueryBuilder $queryBuilder, $value)
    {
        if (!empty($value)) {
            $alias = $this->getMainAlias($queryBuilder);
            $queryBuilder->linkInner("{$alias}.messages")
                ->andWhere('messages.body LIKE :message_substring')
                ->setParameter('message_substring', '%' . $value . '%');
        }
    }

    /**
     * @inheritdoc
     */
    protected function prepareCndOrderBy(\Doctrine\ORM\QueryBuilder $queryBuilder, array $value)
    {
        if (is_array($value) && $value[0] == 'read_messages') {
            $alias = $this->getMainAlias($queryBuilder);
            $queryBuilder->linkInner("{$alias}.messages")
                ->linkLeft('messages.readers', 'r0', \Doctrine\ORM\Query\Expr\Join::WITH, 'r0.reader = :reader')
                ->addSelect('IF(COUNT(messages) = SUM(IF(r0.id IS NULL, 0, 1)), 1, 0) as HIDDEN read_order')
                ->addSelect('MAX(messages.date) as HIDDEN message_date_order')
                ->addOrderBy('read_order', 'asc')
                ->addOrderBy('message_date_order', 'desc')
                ->setParameter('reader', $value[2]);

        } else {
            parent::prepareCndOrderBy($queryBuilder, $value);
        }
    }

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param \XLite\Model\Profile       $value        Condition data
     *
     * @return void
     */
    protected function prepareCndOrdersConditions(\Doctrine\ORM\QueryBuilder $queryBuilder, $value)
    {
        if ($value) {
            $alias = $this->getMainAlias($queryBuilder);
            if (
                \XLite\Module\XC\VendorMessages\Main::isWarehouse()
                && \XLite\Module\XC\VendorMessages\Main::isVendorAllowedToCommunicate()
            ) {
                $queryBuilder->linkLeft("{$alias}.order", 'o')
                    ->andWhere('o.order_id IS NULL OR o.parent IS NOT NULL');
            } else {
                $queryBuilder->linkLeft("{$alias}.order", 'o')
                    ->andWhere('o.order_id IS NULL OR o.orderNumber IS NOT NULL');
            }
        }
    }

    /**
     * Find users dialogue
     *
     * @param \XLite\Model\Profile $profile1
     * @param \XLite\Model\Profile $profile2
     *
     * @return \XLite\Module\XC\VendorMessages\Model\Conversation | null
     */
    public function findDialogue($profile1, $profile2)
    {
        if ($profile1 && $profile2) {
            $qb = $this->createQueryBuilder();
            $alias = $this->getMainAlias($qb);

            $qb->linkInner("{$alias}.members", 'memb')
                ->andWhere(":profile1 MEMBER OF {$alias}.members")
                ->andWhere(":profile2 MEMBER OF {$alias}.members")
                ->andWhere("{$alias}.order IS NULL")
                ->having("COUNT(memb) = 2")
                ->groupBy("{$alias}.id")
                ->setParameter('profile1', $profile1)
                ->setParameter('profile2', $profile2);

            return $qb->getSingleResult();
        }

        return null;
    }
}