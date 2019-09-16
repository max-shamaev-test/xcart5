<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Executor\Step\Execute;

use FilesystemIterator;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Silex\Application;
use XCart\Bus\Client\MarketplaceClient;
use XCart\Bus\Core\Annotations\RebuildStep;
use XCart\Bus\Domain\Module;
use XCart\Bus\Domain\ModuleFilesFactory;
use XCart\Bus\Exception\Rebuild\AbortException;
use XCart\Bus\Exception\Rebuild\HoldException;
use XCart\Bus\Exception\RebuildException;
use XCart\Bus\Query\Data\InstalledModulesDataSource;
use XCart\Bus\Query\Data\KnownHashesCacheDataSource;
use XCart\Bus\Query\Types\Input\RebuildActionParamsType;
use XCart\Bus\Rebuild\Executor\ScriptState;
use XCart\Bus\Rebuild\Executor\Step\StepInterface;
use XCart\Bus\Rebuild\Executor\StepState;
use XCart\Bus\Rebuild\Scenario\ChangeUnitProcessor;
use XCart\Bus\System\FilesystemInterface;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service(arguments={"logger"="XCart\Bus\Core\Logger\Rebuild"})
 * @RebuildStep(script = "redeploy", weight = "3000")
 * @RebuildStep(script = "self-upgrade", weight = "3000")
 */
class CheckPacks implements StepInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var KnownHashesCacheDataSource
     */
    private $knownHashesCacheDataSource;

    /**
     * @var InstalledModulesDataSource
     */
    private $installedModulesDataSource;

    /**
     * @var ModuleFilesFactory
     */
    private $moduleFilesFactory;

    /**
     * @var MarketplaceClient
     */
    private $marketplaceClient;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $rebuildId;

    /**
     * @param Application                $app
     * @param KnownHashesCacheDataSource $knownHashesCacheDataSource
     * @param InstalledModulesDataSource $installedModulesDataSource
     * @param ModuleFilesFactory         $moduleFilesFactory
     * @param MarketplaceClient          $marketplaceClient
     * @param FilesystemInterface        $filesystem
     * @param LoggerInterface            $logger
     *
     * @return static
     *
     * @Service\Constructor
     * @codeCoverageIgnore
     */
    public static function serviceConstructor(
        Application $app,
        KnownHashesCacheDataSource $knownHashesCacheDataSource,
        InstalledModulesDataSource $installedModulesDataSource,
        ModuleFilesFactory $moduleFilesFactory,
        MarketplaceClient $marketplaceClient,
        FilesystemInterface $filesystem,
        LoggerInterface $logger
    ) {
        return new self(
            $app['config']['root_dir'],
            $knownHashesCacheDataSource,
            $installedModulesDataSource,
            $moduleFilesFactory,
            $marketplaceClient,
            $filesystem,
            $logger
        );
    }

    /**
     * @param string                     $rootDir
     * @param KnownHashesCacheDataSource $knownHashesCacheDataSource
     * @param InstalledModulesDataSource $installedModulesDataSource
     * @param ModuleFilesFactory         $moduleFilesFactory
     * @param MarketplaceClient          $marketplaceClient
     * @param FilesystemInterface        $filesystem
     * @param LoggerInterface            $logger
     */
    public function __construct(
        $rootDir,
        KnownHashesCacheDataSource $knownHashesCacheDataSource,
        InstalledModulesDataSource $installedModulesDataSource,
        ModuleFilesFactory $moduleFilesFactory,
        MarketplaceClient $marketplaceClient,
        FilesystemInterface $filesystem,
        LoggerInterface $logger
    ) {
        $this->rootDir                    = $rootDir;
        $this->knownHashesCacheDataSource = $knownHashesCacheDataSource;
        $this->installedModulesDataSource = $installedModulesDataSource;
        $this->moduleFilesFactory         = $moduleFilesFactory;
        $this->marketplaceClient          = $marketplaceClient;
        $this->filesystem                 = $filesystem;
        $this->logger                     = $logger;
    }

    /**
     * @param ScriptState $scriptState
     *
     * @return int
     */
    public function getProgressMax(ScriptState $scriptState): int
    {
        return $scriptState->isStepCompleted(UnpackPacks::class)
            ? count($this->getTransitions($scriptState))
            : count($this->filterScriptTransitions($scriptState->transitions));
    }

    /**
     * @param ScriptState $scriptState
     * @param StepState   $stepState
     *
     * @return StepState
     */
    public function initialize(ScriptState $scriptState, StepState $stepState = null): StepState
    {
        $transitions = $this->getTransitions($scriptState);

        $this->logger->debug(
            __METHOD__,
            [
                'id'          => $scriptState->id,
                'transitions' => $transitions,
            ]
        );

        $state = new StepState([
            'id'                  => static::class,
            'state'               => StepState::STATE_INITIALIZED,
            'rebuildId'           => $scriptState->id,
            'remainTransitions'   => $transitions,
            'finishedTransitions' => [],
            'currentActionInfo'   => [],
            'finishedActionInfo'  => [],
            'data'                => [
                'modified'  => [],
                'preserved' => [],
            ],
            'progressMax'         => $this->getProgressMax($scriptState),
            'progressValue'       => 0,
        ]);

        $state->currentActionInfo = $this->getCurrentActionInfoMessage($state);

        return $state;
    }

    /**
     * If there are modified files after last transition processed we hold step execution
     * On release action do nothing (just check if all transitions are processed)
     *
     * @param StepState $state
     * @param string    $action
     * @param array     $params
     *
     * @return StepState
     * @throws RebuildException
     */
    public function execute(StepState $state, $action = self::ACTION_EXECUTE, array $params = []): StepState
    {
        $this->rebuildId = $state->rebuildId;
        $data            = $state->data;

        switch ($action) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::ACTION_RETRY:
                $state->remainTransitions   = $state->finishedTransitions;
                $state->finishedTransitions = [];
                $state->data                = [
                    'modified'  => [],
                    'preserved' => [],
                ];
                $state->progressValue       = 0;
            // continue execution
            case self::ACTION_EXECUTE:
                $state = $this->processTransition($state);

                if (empty($state->remainTransitions) && !empty($state->data['modified'])) {
                    throw HoldException::fromCheckStepModifiedFilesPresent($state);
                }

                break;

            case self::ACTION_RELEASE:
                $preserved = $params['replaceModified'] === RebuildActionParamsType::KEEP_SELECTED
                    ? $params['filesToKeep']
                    : [];

                $data['preserved'] = array_merge($data['preserved'], $preserved);

                $state->data = $data;

                break;
            case self::ACTION_SKIP_STEP:
                $state = $this->skipStep($state);

                break;
            default:
                break;
        }

        $state->state = !empty($state->remainTransitions)
            ? StepState::STATE_IN_PROGRESS
            : StepState::STATE_FINISHED_SUCCESSFULLY;

        $state->currentActionInfo  = $this->getCurrentActionInfoMessage($state);
        $state->finishedActionInfo = $this->getFinishedActionInfoMessage($state);

        return $state;
    }

    /**
     * @param ScriptState $scriptState
     *
     * @return array
     */
    private function getTransitions(ScriptState $scriptState): array
    {
        $parentStepState = $scriptState->getCompletedStepState(UnpackPacks::class);
        if ($parentStepState) {
            return array_map(static function ($transition) {
                return [
                    'id'             => $transition['id'],
                    'transition'     => $transition['transition'],
                    'version_before' => $transition['version_before'],
                    'version_after'  => $transition['version_after'],
                    'pack_dir'       => $transition['pack_dir'],
                ];
            }, $parentStepState->finishedTransitions ?: []);
        }

        return [];
    }

    /**
     * @param StepState $state
     *
     * @return string[]
     */
    private function getCurrentActionInfoMessage(StepState $state): array
    {
        $finished = count($state->finishedTransitions);
        $total    = $finished + count($state->remainTransitions);
        $data     = $state->data;

        if ($total !== $finished) {
            return $data['modified']
                ? [[
                       'message' => 'rebuild.check_packs.state.warnings',
                       'params'  => [$finished, $total, count($data['modified'])],
                   ]]
                : [[
                       'message' => 'rebuild.check_packs.state',
                       'params'  => [$finished, $total],
                   ]];
        }

        return [];
    }

    /**
     * @param StepState $state
     *
     * @return string[]
     */
    private function getFinishedActionInfoMessage(StepState $state): array
    {
        $finished = count($state->finishedTransitions);
        $total    = $finished + count($state->remainTransitions);
        $data     = $state->data;

        if ($total === $finished) {
            return $data['modified']
                ? [[
                       'message' => 'rebuild.check_packs.state.finished.warnings',
                       'params'  => [$finished, $total, count($data['modified'])],
                   ]]
                : [[
                       'message' => 'rebuild.check_packs.state.finished',
                       'params'  => [$finished, $total],
                   ]];
        }

        return [];
    }

    /**
     * @param StepState $state
     *
     * @return StepState
     * @throws RebuildException
     */
    private function processTransition(StepState $state): StepState
    {
        $remainTransitions = $state->remainTransitions;
        if (empty($remainTransitions)) {
            return $state;
        }

        $finishedTransitions = $state->finishedTransitions;
        $transition          = current($remainTransitions);
        $id                  = $transition['id'];

        $progressValue = $state->progressValue;

        $data      = $state->data;
        $modified  = $data['modified'];
        $preserved = $data['preserved'];

        $this->logger->debug(
            sprintf('Check package: %s', $transition['pack_dir']),
            [
                'id' => $this->rebuildId,
            ]
        );

        // main action
        [$modifiedForTransition, $preservedForTransition, $originalFiles, $newFiles]
            = $this->getFilesListsByTransition($transition);

        if ($modifiedForTransition || $preservedForTransition) {
            $this->logger->debug(
                sprintf('Package checked: %s', $transition['pack_dir']),
                [
                    'id'        => $this->rebuildId,
                    'modified'  => $modifiedForTransition,
                    'preserved' => $preservedForTransition,
                ]
            );
        } else {
            $this->logger->debug(
                sprintf('Package checked: %s', $transition['pack_dir']),
                [
                    'id' => $this->rebuildId,
                ]
            );
        }

        // update state
        $data['modified']             = array_merge($modified, $modifiedForTransition);
        $data['preserved']            = array_merge($preserved, $preservedForTransition);
        $transition['original_files'] = $this->filterFilesToUpdate($originalFiles);
        $transition['new_files']      = $this->filterFilesToUpdate($newFiles);

        $finishedTransitions[$id] = $transition;
        unset($remainTransitions[$id]);
        $progressValue++;

        // save state
        $state->data                = $data;
        $state->remainTransitions   = $remainTransitions;
        $state->finishedTransitions = $finishedTransitions;
        $state->progressValue       = $progressValue;

        return $state;
    }

    /**
     * @param StepState $state
     *
     * @return StepState
     */
    private function skipStep(StepState $state): StepState
    {
        $state->finishedTransitions = array_map(function ($item) {
            /** @var Module $module */
            $module      = $this->installedModulesDataSource->find($item['id']);
            $moduleFiles = $this->moduleFilesFactory->getModuleFilesIterator($item['id'], $module->skins ?? []);

            return $item + [
                    'original_files' => [],
                    'new_files'      => array_keys($moduleFiles),
                ];
        }, $state->remainTransitions);

        $state->remainTransitions = [];
        $state->progressValue     = $state->progressMax;

        return $state;
    }

    /**
     * @param array $transition
     *
     * @return array Array of three elements:
     *               - modified files (original hash not equal actual one)
     *               - original files (from marketplace)
     *               - new files (from package)
     * @throws RebuildException
     */
    private function getFilesListsByTransition($transition): array
    {
        $originalHashes = $transition['version_before']
            ? $this->getOriginalHashes($transition['id'], $transition['version_before'])
            : [];

        [$modified, $preserved] = $this->getModifiedFiles($originalHashes);

        $newHashes = $this->getNewHashes($transition['pack_dir']);

        $this->knownHashesCacheDataSource->saveOne(
            $newHashes,
            md5($transition['id'] . '|' . $transition['version_after'])
        );

        return [
            $modified,
            $preserved,
            array_keys($originalHashes),
            array_keys($newHashes),
        ];
    }

    /**
     * @param string $id
     * @param string $version
     *
     * @return array
     * @throws RebuildException
     */
    private function getOriginalHashes($id, $version): array
    {
        $hashes = $this->knownHashesCacheDataSource->find(md5($id . '|' . $version));
        if ($hashes) {
            return $hashes;
        }

        $response = $this->marketplaceClient->getHashes($id, $version);

        if (!is_array($response)) {
            throw AbortException::fromCheckStepInvalidResponse($id);
        }

        if (!empty($response['error'])) {
            throw AbortException::fromCheckStepWrongResponse($id, $response['message']);
        }

        return $response;
    }

    /**
     * Returns existing files with changed hash
     *
     * @param array $originalHashes
     *
     * @return array
     */
    private function getModifiedFiles(array $originalHashes): array
    {
        $list = [
            'modified'  => [],
            'preserved' => [],
        ];

        foreach ($originalHashes as $path => $hash) {
            if ($this->ignoreOriginalFile($path)) {
                continue;
            }

            $fullPath = $this->rootDir . $path;
            if (file_exists($fullPath)
                && md5_file($fullPath) !== $hash
            ) {
                if ($this->preserveModifiedFile($path)) {
                    $list['preserved'][] = $path;
                } else {
                    $list['modified'][] = $path;
                }
            }
        }

        return array_values($list);
    }

    /**
     * @param string $packageDir
     *
     * @return array
     * @throws RebuildException
     */
    private function readHashesFromFile($packageDir): array
    {
        $path = $packageDir . '/.hash';

        if (!$this->filesystem->exists($path)) {
            return [];
        }

        if (!($data = @json_decode(file_get_contents($path), true))) {
            throw AbortException::fromCheckStepWrongHashFile($path);
        }

        return $data;
    }

    /**
     * @param string $packageDir
     *
     * @return array
     * @throws RebuildException
     */
    private function getNewHashes($packageDir): array
    {
        $newHashes = $this->readHashesFromFile($packageDir);
        if ($newHashes) {
            return $newHashes;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($packageDir, FilesystemIterator::SKIP_DOTS)
        );

        $result = [];
        foreach ($iterator as $filePath => $fileInfo) {
            $path = $this->filesystem->makePathRelative(dirname($filePath), $packageDir);

            $result[($path === './' ? '' : $path) . basename($filePath)] = md5_file($filePath);
        }

        return $result;
    }

    /**
     * @param array[] $transitions
     *
     * @return array[]
     */
    private function filterScriptTransitions($transitions): array
    {
        return array_filter($transitions, static function ($transition) {
            return in_array($transition['transition'], [
                ChangeUnitProcessor::TRANSITION_UPGRADE,
                ChangeUnitProcessor::TRANSITION_INSTALL_ENABLED,
                ChangeUnitProcessor::TRANSITION_INSTALL_DISABLED,
            ], true);
        });
    }

    /**
     * @param string[] $list
     *
     * @return string[]
     */
    private function filterFilesToUpdate($list): array
    {
        return array_filter($list, function ($item) {
            return !$this->isPatternApplicable($item, ['robots.txt']);
        });
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function ignoreOriginalFile($path): bool
    {
        return $this->isPatternApplicable($path, ['var', 'files', 'images', 'sql', 'robots.txt']);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function preserveModifiedFile($path): bool
    {
        return $this->isPatternApplicable($path, ['skins/common/images/flags_svg/', 'changelog/']);
    }

    /**
     * @param string $path
     * @param array  $patterns
     *
     * @return bool
     */
    private function isPatternApplicable($path, $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (strpos($path, $pattern) === 0) {
                return true;
            }
        }

        return false;
    }
}
