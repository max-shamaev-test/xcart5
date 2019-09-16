<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Query\Data\Filter\Module;

use Iterator;
use XCart\Bus\Client\LicenseClient;
use XCart\Bus\Domain\Module;
use XCart\Bus\Query\Data\Filter\AFilter;

class NonFreeEdition extends AFilter
{
    /**
     * @var array
     */
    private $freeLicenseInfo;

    /**
     * @param Iterator      $iterator
     * @param string        $field
     * @param mixed         $data
     * @param LicenseClient $licenseClient
     */
    public function __construct(
        Iterator $iterator,
        $field,
        $data,
        LicenseClient $licenseClient
    ) {
        parent::__construct($iterator, $field, $data);

        $this->freeLicenseInfo = $licenseClient->getFreeLicenseInfo();
    }

    /**
     * @return bool
     */
    public function accept()
    {
        /** @var Module $item */
        $item = $this->getInnerIterator()->current();

        $freeEditionName = $this->freeLicenseInfo['editionName'];

        return !empty($item->editions) && !in_array($freeEditionName, $item->editions, true);
    }
}
