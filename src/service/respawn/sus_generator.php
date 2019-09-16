<?php

include 'sus_internals.php';

$susVersion = \XCart\SUS\Config::$currentVersion;

$fileManger = new \XCart\SUS\FileManager();

\XCart\SUS\Config::$xcartRoot = __DIR__ . $fileManger->preparePath('/../..');

$logPath = __DIR__ . $fileManger->preparePath('/../../var/log/' . date('Y/m'));
$fileManger->mkdir($logPath);
XCart\SUS\Logger::setLogFile($logPath . \DIRECTORY_SEPARATOR . 'sus.log.' . date('Y-m-d') . '.php');

try {
    $source = dirname(__DIR__);
    $package = __DIR__ . $fileManger->preparePath('/../../var/werel-' . $susVersion . '.tar');

    XCart\SUS\Logger::log('Build package', [
        'source' => $source,
        'package' => $package,
    ]);

    \XCart\SUS\Package::buildFromPath($source, $package);

} catch (\XCart\SUS\Exception $e) {
    XCart\SUS\Logger::log($e->getMessage(), $e->getData() ?: null);

    http_response_code($e->getCode() ?: 500);
    die();
}
