<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Domain;

use BadMethodCallException;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use UnexpectedValueException;
use XCart\Bus\Exception\PackageException;
use XCart\Bus\System\Filesystem;
use XCart\Bus\System\ResourceChecker;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class Package
{
    public const HASH_FILE = '.hash';

    /**
     * @var string
     */
    private $packagesDir;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var Module
     */
    private $module;

    /**
     * @var boolean
     */
    private $canCompress;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @param Application        $app
     * @param Filesystem         $fileSystem
     * @param ModuleInfoProvider $moduleInfoProvider
     */
    public function __construct(
        Application $app,
        Filesystem $fileSystem,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->packagesDir        = $app['config']['module_packs_dir'];
        $this->rootDir            = $app['config']['root_dir'];
        $this->fileSystem         = $fileSystem;
        $this->moduleInfoProvider = $moduleInfoProvider;
        $this->canCompress = ResourceChecker::PharIsInstalled()
            ? \Phar::canCompress(\Phar::GZ)
            : false;
    }

    /**
     * @param Module $module
     *
     * @return self
     */
    public function fromModule(Module $module): self
    {
        $package = clone $this;

        $package->module = $module;

        return $package;
    }

    /**
     * @param string $filePath
     *
     * @return self
     * @throws PackageException
     */
    public function fromFile($filePath): ?self
    {
        try {
            $phar     = new \PharData($filePath);
            $metadata = $phar->getMetadata();

            $module  = Module::fromPackageMetadata($metadata);
            $package = $this->fromModule($module);

            $this->fileSystem->rename($filePath, $this->packagesDir . $package->getFileName(), true);

            return $package;

        } catch (UnexpectedValueException $e) {
            $this->fileSystem->remove($filePath);
            throw PackageException::fromNonPharArchive();

        } catch (BadMethodCallException $e) {
            throw PackageException::fromGenericError($e);
        }
    }

    /**
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function createPackage(): string
    {
        if ($this->module === null) {
            return '';
        }

        $iterator = $this->getIterator();
        $fullPath = $this->packagesDir . $this->getFileName();

        $this->fileSystem->mkdir($this->packagesDir);
        $this->fileSystem->remove($fullPath);

        $phar = new \PharData($fullPath);
        $phar->buildFromIterator($iterator, $this->rootDir);

        $phar->setMetadata($this->module->toPackageMetadata());

        $phar->addFromString(static::HASH_FILE, json_encode($this->getHash($iterator)));

        if ($this->canCompress) {
            $phar = $phar->compress(\Phar::GZ);
            // Truncates version, see https://bugs.php.net/bug.php?id=58852
            $this->fileSystem->rename($phar->getPath(), $fullPath, true);
        }

        return $fullPath;
    }

    /**
     * @param string $file
     *
     * @return self
     * @throws PackageException
     */
    public function loadPackage($file): self
    {
        if (!$file) {
            throw PackageException::fromNoFile();
        }

        $fullPath = $this->packagesDir . basename($file);

        $this->fileSystem->mkdir($this->packagesDir);
        $this->fileSystem->remove($fullPath);

        try {
            $this->fileSystem->rename($file, $fullPath, true);
        } catch (FileException $e) {
            throw PackageException::fromGenericError($e);
        }

        return $this->fromFile($fullPath);
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        $fileName = $this->module
            ? $this->module->id . '.' . $this->module->version
            : '';

        return $fileName . ($this->canCompress ? '.tgz' : '.tar');
    }

    /**
     * @return array
     */
    public function getModuleFilesList(): array
    {
        return $this->getFilesStructure(
            $this->getIterator()
        );
    }

    /**
     * @return \AppendIterator
     */
    private function getIterator(): \AppendIterator
    {
        $result = new \AppendIterator();

        $moduleInfo = $this->moduleInfoProvider->getModuleInfo($this->module->id);

        if (isset($moduleInfo['directories'])) {
            foreach ((array) $moduleInfo['directories'] as $directory) {
                if (is_dir($directory)) {
                    $result->append(new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * @param \Iterator $iterator
     *
     * @return array
     */
    private function getHash(\Iterator $iterator): array
    {
        $files = $this->getFilesStructure($iterator);

        return array_map('md5_file', $files);
    }

    /**
     * @param \Iterator $iterator
     *
     * @return array
     */
    private function getFilesStructure(\Iterator $iterator): array
    {
        $result = [];

        foreach ($iterator as $filePath => $fileInfo) {
            $path = $this->fileSystem->makePathRelative(dirname($filePath), $this->rootDir);

            $result[$path . basename($filePath)] = $filePath;
        }

        return $result;
    }
}
