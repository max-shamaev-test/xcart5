<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\FileAttachments\Model\Repo\Product;

/**
 * Product attachment's repository
 */
class Attachment extends \XLite\Model\Repo\Base\I18n
{
    const P_PRODUCT = 'product';

    /**
     * Prepare certain search condition
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder Query builder to prepare
     * @param mixed                      $value        Condition data
     * @param boolean                    $countOnly    "Count only" flag. Do not need to add "order by" clauses if only count is needed.
     *
     * @return void
     */
    protected function prepareCndProduct(\Doctrine\ORM\QueryBuilder $queryBuilder, $value, $countOnly)
    {
        $alias = $this->getMainAlias($queryBuilder);
        if ($value instanceOf \XLite\Model\Product) {
            $queryBuilder->andWhere($alias . '.product = :product')
                ->setParameter('product', $value);

        } else {
            $queryBuilder->leftJoin($alias . '.product', 'product')
                ->andWhere('product.product_id = :productId')
                ->setParameter('productId', $value);
        }
    }

    /**
     * Returns max orderby for attachments by selected product
     *
     * @param \XLite\Model\Product $product
     *
     * @return integer
     */
    public function getMaxAttachmentOrderByForProduct(\XLite\Model\Product $product)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('MAX(a.orderby)')
            ->andWhere('a.product = :product')
            ->setParameter('product', $product);

        return (integer)$qb->getSingleScalarResult();
    }
}
