<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\AmazonS3Images\Core\Job;

use XLite\Core\Job\JobAbstract;
use XLite\Core\Job\SerializeModels;

class ResizeIconS3 extends JobAbstract
{
    use SerializeModels;

    /**
     * @var \XLite\Model\Base\Image
     */
    private $item;
    /**
     * @var
     */
    private $path;
    /**
     * @var
     */
    private $width;
    /**
     * @var
     */
    private $height;

    public function __construct(\XLite\Model\Base\Image $item, $width, $height, $path)
    {
        parent::__construct();

        $this->item = $item;

        $this->width = $width;
        $this->height = $height;
        $this->path = $path;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->markAsStarted();
        $this->item->resizeIconAsync($this->width, $this->height, $this->path);
        $this->markAsFinished();
    }

    public function getName()
    {
        $item = $this->item;

        return 'Moving resized image #' . $item->getId();
    }

    /**
     * @return mixed
     */
    public function getPreferredQueue()
    {
        return \XLite\Core\Queue\Driver::QUEUE_HIGH_PRIORITY;
    }
}
