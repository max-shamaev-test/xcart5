<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Marketplace\Request;

use XCart\Marketplace\IValidator;
use XCart\Marketplace\Validator\Callback;

class ShippingMethods extends AFileRequest
{
    /**
     * @return bool
     */
    public function ignoreTransportErrors(): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getDefaultResponse()
    {
        return json_encode(new \stdClass());
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        $coreMajorVersion = static::getElement($this->params->getParams(), 'core_major_version');
        if ($coreMajorVersion) {
            return sprintf('/sites/default/files/shm-%s.json', $coreMajorVersion);
        }

        return null;
    }

    /**
     * @return IValidator
     */
    public function getValidator(): IValidator
    {
        return new Callback(function ($data) {
            return is_array($data);
        });
    }
}
