<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use XCart\Bus\Domain\Package as DomainPackage;
use XCart\Bus\Exception\PackageException;
use XCart\Bus\Exception\UploadException;
use XCart\Bus\Query\Data\InstalledModulesDataSource;
use XCart\Bus\Query\Data\ScenarioDataSource;
use XCart\Bus\Query\Data\UploadedModulesDataSource;
use XCart\Bus\Rebuild\Scenario\ChangeUnitProcessor;
use XCart\Bus\System\Uploader;
use XCart\SilexAnnotations\Annotations\Router;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 * @Router\Controller()
 */
class Package
{
    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * @var ChangeUnitProcessor
     */
    private $changeUnitProcessor;

    /**
     * @var ScenarioDataSource
     */
    private $scenarioDataSource;

    /**
     * @var UploadedModulesDataSource
     */
    private $uploadedModulesDataSource;

    /**
     * @var InstalledModulesDataSource
     */
    private $installedModulesDataSource;

    /**
     * @var DomainPackage
     */
    private $package;

    /**
     * @param InstalledModulesDataSource $installedModulesDataSource
     * @param UploadedModulesDataSource  $uploadedModulesDataSource
     * @param DomainPackage              $package
     * @param ScenarioDataSource         $scenarioDataSource
     * @param ChangeUnitProcessor        $changeUnitProcessor
     * @param Uploader                   $uploader
     */
    public function __construct(
        InstalledModulesDataSource $installedModulesDataSource,
        UploadedModulesDataSource $uploadedModulesDataSource,
        DomainPackage $package,
        ScenarioDataSource $scenarioDataSource,
        ChangeUnitProcessor $changeUnitProcessor,
        Uploader $uploader
    ) {
        $this->package                    = $package;
        $this->installedModulesDataSource = $installedModulesDataSource;
        $this->uploadedModulesDataSource  = $uploadedModulesDataSource;
        $this->scenarioDataSource         = $scenarioDataSource;
        $this->changeUnitProcessor        = $changeUnitProcessor;
        $this->uploader                   = $uploader;
    }

    /**
     * @param Request $request
     * @param string  $moduleId
     *
     * @return Response
     * @Router\Route(
     *     @Router\Request(method="GET", uri="/download/{moduleId}"),
     *     @Router\Before("XCart\Bus\Controller\Auth:authChecker")
     * )
     */
    public function downloadAction(Request $request, $moduleId): Response
    {
        $module = $this->installedModulesDataSource->find($moduleId);
        if ($module) {
            $package = $this->package->fromModule($module);

            return (new BinaryFileResponse($package->createPackage()))
                ->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $package->getFileName()
                )
                ->deleteFileAfterSend(true);
        }

        return new Response('We are sorry, but something went terribly wrong.', 404);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @Router\Route(
     *     @Router\Request(method="POST", uri="/package/chunk"),
     *     @Router\Before("XCart\Bus\Controller\Auth:authChecker")
     * )
     */
    public function uploadAction(Request $request): Response
    {
        try {
            $this->uploader->processChunked($request);
        } catch (UploadException $e) {
            return $this->getExceptionResponse($e);
        }

        return new Response('OK', 200);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @Router\Route(
     *     @Router\Request(method="DELETE", uri="/package"),
     *     @Router\Before("XCart\Bus\Controller\Auth:authChecker")
     * )
     */
    public function cancelUpload(Request $request): Response
    {
        try {
            $this->uploader->cancelUpload($request);
        } catch (UploadException $e) {
            return $this->getExceptionResponse($e);
        }

        return new Response('OK', 200);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @Router\Route(
     *     @Router\Request(method="POST", uri="/package"),
     *     @Router\Before("XCart\Bus\Controller\Auth:authChecker")
     * )
     */
    public function finalizeAction(Request $request): Response
    {
        try {
            $filePath = $this->uploader->finalizeUpload($request);
            // TODO: validate uploaded file

            $package = $this->package->loadPackage($filePath);
            $this->uploadedModulesDataSource->saveFromPackage($package);

            $this->updateScenario($package);
        } catch (UploadException $e) {
            return $this->getExceptionResponse($e);
        } catch (PackageException $e) {
            return $this->getExceptionResponse($e);
        }

        return new Response('OK', 200);
    }

    /**
     * @param DomainPackage $package
     *
     * @throws PackageException
     */
    private function updateScenario(DomainPackage $package): void
    {
        $module      = $package->getModule();
        $changeUnits = [
            [
                'id'      => $module->id,
                'version' => $module->version,
                'install' => true,
            ],
        ];

        try {
            $scenario         = $this->changeUnitProcessor->process([], $changeUnits);
            $scenario['id']   = 'uploadAddons';
            $scenario['type'] = 'common';
            $scenario['date'] = time();

            $this->scenarioDataSource->saveOne($scenario);
        } catch (\Exception $e) {
            throw PackageException::fromGenericError($e->getMessage());
        }
    }

    /**
     * @param \Exception $exception
     *
     * @return JsonResponse
     */
    private function getExceptionResponse(\Exception $exception)
    {
        $alert = [
            'type'    => 'danger',
            'message' => $exception->getMessage(),
            'params'  => method_exists($exception, 'getParams') ? $exception->getParams() : [],
        ];

        return new JsonResponse($alert, $exception->getCode());
    }
}
