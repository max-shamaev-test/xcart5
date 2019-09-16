<?php
/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\System;

use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class ResourceChecker
{
    /**
     * @var int
     */
    private $startTime;

    /**
     * @var int
     */
    private $maxExecutionTime;

    public function __construct()
    {
        $this->maxExecutionTime = (int) ((ini_get('max_execution_time') ?: 30) * 10000);
    }

    public function start(): void
    {
        $this->startTime = $this->getTime();
    }

    /**
     * @return int
     */
    public function timeRemain(): int
    {
        return $this->maxExecutionTime - $this->getTimePassed();
    }

    /**
     * @return int
     */
    public function getTimePassed(): int
    {
        return $this->getTime() - $this->startTime;
    }

    /**
     * @return int
     */
    private function getTime()
    {
        return (int) (microtime(true) * 10000);
    }
}
