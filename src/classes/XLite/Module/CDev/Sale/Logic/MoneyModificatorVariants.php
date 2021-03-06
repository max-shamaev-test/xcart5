<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\Sale\Logic;

use XLite\Core\Database;

/**
 * Net price modificator
 * @Decorator\Depend("XC\ProductVariants")
 */
class MoneyModificatorVariants extends \XLite\Module\CDev\Sale\Logic\MoneyModificator implements \XLite\Base\IDecorator
{
    /**
     * Check modificator - apply or not
     *
     * @param \XLite\Model\AEntity $model     Model
     * @param string               $property  Model's property
     * @param array                $behaviors Behaviors
     * @param string               $purpose   Purpose
     *
     * @return boolean
     */
    static public function isApply(\XLite\Model\AEntity $model, $property, array $behaviors, $purpose)
    {
        $obj = self::getObject($model);
        if ($obj instanceOf \XLite\Module\XC\ProductVariants\Model\ProductVariant) {
            if ($obj->getProduct()->isWholesalePricesEnabled()) {
                if ($obj->getProduct()->getApplySaleToWholesale() || $obj->isFirstWholesaleTier())
                    return !$obj->getDefaultSale();
            } else {
                return !$obj->getDefaultSale();
            }
        }

        return parent::isApply($model, $property, $behaviors, $purpose);
    }

    /**
     * Modify money
     *
     * @param \XLite\Model\AEntity $model Model
     *
     * @return \XLite\Model\AEntity
     */
    static protected function getObject(\XLite\Model\AEntity $model)
    {
        $object = $model;
        if ($object instanceof \XLite\Model\OrderItem) {
            $object = $object->getVariant();
        }

        if (is_a($object ,'\XLite\Module\CDev\Wholesale\Model\ProductVariantWholesalePrice')) {
            $object = $object->getProductVariant();
        }

        if ($object instanceof \XLite\Module\XC\ProductVariants\Model\ProductVariant
            && !$object->getDefaultSale()
        ) {
            return $object;
        }

        return parent::getObject($model);
    }
}
