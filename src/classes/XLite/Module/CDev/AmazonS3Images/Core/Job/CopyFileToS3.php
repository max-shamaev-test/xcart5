<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\AmazonS3Images\Core\Job;

use XLite\Core\Job\JobAbstract;
use XLite\Core\Job\SerializeModels;
use XLite\Core\Queue\Scheduler\SchedulerService;

class CopyFileToS3 extends JobAbstract
{
    const MAX_TRY_COUNT = 5;

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
    private $basename;
    /**
     * @var int
     */
    private $tryCount;

    public function __construct(\XLite\Model\Base\Image $item, $path, $basename, $tryCount = 0)
    {
        parent::__construct();

        $this->item = $item;
        $this->path = $path;
        $this->basename = $basename;
        $this->tryCount = $tryCount;
    }

    public function getName()
    {
        return 'Moving image #' . $this->item->getId();
    }

    /**
     * @return void
     */
    public function handle()
    {
        /** @var \XLite\Model\Repo\Base\Image $repo */
        $repo = $this->item->getRepository();
        $foundItems = $repo->findByFullPath($this->path, new $this->item);

        if ($this->tryCount > static::MAX_TRY_COUNT) {
            \XLite\Logger::logCustom('queue', 'Cannot upload ' . $this->path .' image');
            return;
        }

        // This is needed because item can be invalid(not in db) at the moment
        if (!$foundItems) {
            sleep(1);
            $job = new CopyFileToS3($this->item, $this->path, $this->basename, $this->tryCount + 1);
            SchedulerService::createDefaultJobScheduler()->schedule($job);
            return;
        }

        $this->item = reset($foundItems);

        $this->markAsStarted();
        $localPath = $this->item->isURL() ? null : $this->item->getStoragePath();
        $allowRemove = $this->item->isAllowRemoveFile();

        $result = $this->item->loadFromLocalFileAsync($this->path, $this->basename);

        \XLite\Core\Database::getEM()->persist($this->item);
        \XLite\Core\Database::getEM()->flush();

        if ($result && $allowRemove && $localPath && \Includes\Utils\FileManager::isExists($localPath)) {
            \Includes\Utils\FileManager::deleteFile($localPath);
        }

        $this->item->prepareSizes();
        $this->markAsFinished();
    }

    /**
     * @return mixed
     */
    public function getPreferredQueue()
    {
        return \XLite\Core\Queue\Driver::QUEUE_HIGH_PRIORITY;
    }
}
