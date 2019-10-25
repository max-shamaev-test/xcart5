<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Query\Data;

use Silex\Application;
use XCart\Bus\Client\MarketplaceClient;
use XCart\Bus\Domain\Module;
use XCart\Bus\Domain\Storage\StorageInterface;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class MarketplaceModulesDataSource extends AMarketplaceCachedDataSource
{
    /**
     * @var UploadedModulesDataSource
     */
    private $uploadedModulesDataSource;

    /**
     * @var LicenseDataSource
     */
    private $licenseDataSource;

    private $localCache = [];

    /**
     * @param Application               $app
     * @param MarketplaceClient         $client
     * @param SetDataSource             $setDataSource
     * @param UploadedModulesDataSource $uploadedModulesDataSource
     * @param LicenseDataSource         $licenseDataSource
     * @param StorageInterface          $storage
     *
     * @return static
     *
     * @Service\Constructor
     * @codeCoverageIgnore
     */
    public static function serviceConstructor(
        Application $app,
        MarketplaceClient $client,
        SetDataSource $setDataSource,
        UploadedModulesDataSource $uploadedModulesDataSource,
        LicenseDataSource $licenseDataSource,
        StorageInterface $storage
    ) {
        return new static(
            $client,
            $setDataSource,
            $uploadedModulesDataSource,
            $licenseDataSource,
            $storage->build($app['config']['cache_dir'], 'busMarketplaceModulesStorage')
        );
    }

    /**
     * @param MarketplaceClient         $client
     * @param SetDataSource             $setDataSource
     * @param UploadedModulesDataSource $uploadedModulesDataSource
     * @param LicenseDataSource         $licenseDataSource
     * @param StorageInterface          $storage
     */
    public function __construct(
        MarketplaceClient $client,
        SetDataSource $setDataSource,
        UploadedModulesDataSource $uploadedModulesDataSource,
        LicenseDataSource $licenseDataSource,
        StorageInterface $storage
    ) {
        parent::__construct($client, $setDataSource, $storage);

        $this->uploadedModulesDataSource = $uploadedModulesDataSource;
        $this->licenseDataSource         = $licenseDataSource;
    }

    /**
     * @param      $id
     * @param null $version
     *
     * @return Module|null
     */
    public function findByVersion($id, $version = null): ?Module
    {
        $key = $id . $version;

        if (!empty($this->localCache[$key])) {
            return $this->localCache[$key];
        }

        $modules = $this->find($id);

        if (!$modules) {
            return null;
        }

        if ($version) {
            $this->localCache[$key] = array_reduce($modules, static function ($carry, $item) use ($version) {
                /** @var Module $carry */
                /** @var Module $item */
                return version_compare($version, $item->version, '=') ? $item : $carry;
            });

            return $this->localCache[$key];
        }

        $this->localCache[$key] = array_reduce($modules, static function ($carry, $item) {
            /** @var Module $carry */
            /** @var Module $item */
            if (!$carry) {
                return $item;
            }

            return version_compare($carry->version, $item->version, '<')
                ? $item
                : $carry;
        });

        return $this->localCache[$key];
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $result = parent::getAll();

        $uploadedModules = $this->uploadedModulesDataSource->getAll();
        if ($uploadedModules) {
            foreach ($uploadedModules as $id => $module) {
                $result[$id][] = $module[0];
            }
        }

        return $result;
    }

    /**
     * @return mixed
     */
    protected function doRequest()
    {
        $keys = [];
        foreach ($this->licenseDataSource->getAll() as $keyInfo) {
            $keys[] = $keyInfo['keyValue'];
        }
        
        if ($keys) {
            $keysInfo = $this->client->getLicenseInfo($keys);
            $this->licenseDataSource->updateAll($keysInfo);
        }

        $modules = [];
        foreach ($this->client->getAllModules() as $id => $versions) {
            $modules[$id] = array_map(static function ($item) {
                return new Module($item);
            }, $versions);
        }

        $cores = [];
        foreach ($this->client->getCores() as $id => $versions) {
            $cores[$id] = array_map(static function ($item) {
                return new Module($item);
            }, $versions);
        }

        return array_merge($modules, $cores);
    }

    /**
     * @return bool
     */
    protected function shouldAddIdToItemOnSave(): bool
    {
        return false;
    }
}
