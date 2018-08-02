<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2018-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/module-marketplace-terms-of-use.html for license details.
 */

return function()
{
    $modifier = \XLite\Core\Database::getRepo('XLite\Model\Order\Modifier')
        ->findOneByClass('\XLite\Module\QSL\SpecialOffersBase\Logic\Order\Modifier\SpecialOffers');
    $modifier->setWeight(40);

    \XLite\Core\Database::getEM()->flush();
};
