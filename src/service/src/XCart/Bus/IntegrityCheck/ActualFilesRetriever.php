<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\IntegrityCheck;

use Silex\Application;
use XCart\Bus\Domain\Module;
use XCart\Bus\Domain\Package;
use XCart\Bus\Domain\Package as DomainPackage;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class ActualFilesRetriever
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var DomainPackage
     */
    private $package;

    /**
     * @var CoreIteratorBuilder
     */
    private $coreIteratorBuilder;

    /**
     * @param Application         $app
     * @param Package             $package
     * @param CoreIteratorBuilder $coreIteratorBuilder
     *
     * @return ActualFilesRetriever
     *
     * @Service\Constructor
     * @codeCoverageIgnore
     */
    public static function serviceConstructor(
        Application $app,
        DomainPackage $package,
        CoreIteratorBuilder $coreIteratorBuilder
    ): ActualFilesRetriever {
        return new self(
            $app['config']['root_dir'],
            $package,
            $coreIteratorBuilder
        );
    }

    /**
     * @param                     $rootDir
     * @param DomainPackage       $package
     * @param CoreIteratorBuilder $coreIteratorBuilder
     */
    public function __construct(
        $rootDir,
        DomainPackage $package,
        CoreIteratorBuilder $coreIteratorBuilder
    ) {
        $this->rootDir             = $rootDir;
        $this->package             = $package;
        $this->coreIteratorBuilder = $coreIteratorBuilder;
    }

    /**
     * @param Module $module
     *
     * @return array
     */
    public function getActualFilesPaths($module): array
    {
        if (!empty($module->id) && $module->id === 'CDev-Core') {
            $iterator = $this->coreIteratorBuilder->getIterator();

            $result = [];

            foreach ($iterator as $absolutePath => $item) {
                /** @var \SplFileInfo $item */
                $prefix = $this->rootDir;

                if (0 === strpos($absolutePath, $prefix)) {
                    $absolutePath = substr($absolutePath, strlen($prefix));
                }
                $result[$absolutePath] = $item->getPathname();
            }

            return $result;
        }

        $package = $this->package->fromModule($module);

        return $package->getModuleFilesList();
    }
}
