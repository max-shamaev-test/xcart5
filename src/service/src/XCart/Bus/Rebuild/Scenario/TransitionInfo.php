<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Scenario;

class TransitionInfo
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $reasonHuman;

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReasonHuman()
    {
        return $this->reasonHuman;
    }

    /**
     * @param string $reasonHuman
     */
    public function setReasonHuman($reasonHuman)
    {
        $this->reasonHuman = $reasonHuman;
    }
}
