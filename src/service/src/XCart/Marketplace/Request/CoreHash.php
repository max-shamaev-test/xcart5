<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Marketplace\Request;

use XCart\Marketplace\Constant;
use XCart\Marketplace\ITransport;
use XCart\Marketplace\IValidator;
use XCart\Marketplace\Validator\Callback;

class CoreHash extends AAPIRequest
{
    /**
     * @return int
     */
    public static function getTransportTTL(): int
    {
        return ITransport::TTL_LONG;
    }

    /**
     * @return string
     */
    public function getAction(): ?string
    {
        return Constant::ACTION_GET_CORE_HASH;
    }

    /**
     * @return IValidator
     */
    public function getValidator(): IValidator
    {
        return new Callback(function ($data) {
            return (bool) $data;
        });
    }

    /**
     * @return array
     */
    protected function getDefaultParams(): array
    {
        return [
            Constant::FIELD_VERSION => [],
        ];
    }
}
