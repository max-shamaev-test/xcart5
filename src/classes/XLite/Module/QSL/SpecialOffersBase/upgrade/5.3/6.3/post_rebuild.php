<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2017-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/module-marketplace-terms-of-use.html for license details.
 */

return function()
{
    $yamlFile = __DIR__ . LC_DS . 'update.yaml';
    if (\Includes\Utils\FileManager::isFileReadable($yamlFile)) {
        \XLite\Core\Database::getInstance()->loadFixturesFromYaml($yamlFile);
    }
    \XLite\Core\Translation::getInstance()->reset();
    \XLite\Core\Database::getEM()->flush();
};
