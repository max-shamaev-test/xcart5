<?php

include 'sus_internals.php';

$susVersion = \XCart\SUS\Config::$currentVersion;

$fileManger = new \XCart\SUS\FileManager();

\XCart\SUS\Config::$xcartRoot = __DIR__ . $fileManger->preparePath('/../..');

$logPath = __DIR__ . $fileManger->preparePath('/../../var/log/' . date('Y/m'));
$fileManger->mkdir($logPath);
XCart\SUS\Logger::setLogFile($logPath . \DIRECTORY_SEPARATOR . 'sus.log.' . date('Y-m-d') . '.php');

try {

    if (isset($_GET['auth_code'])) {
        $susAuthCode = file_get_contents(__DIR__ . \XCart\SUS\Config::$authCodeFile);
        if ((string) $_GET['auth_code'] !== $susAuthCode) {

            throw new \XCart\SUS\Exception('Unauthorised request', $_SERVER, 401);
        }

    } elseif (isset($_COOKIE['bus_token'])
        && strlen($_COOKIE['bus_token']) === 32
        && preg_match('/^[a-f0-9]+$/', $_COOKIE['bus_token'])
    ) {
        try {
            $connectionProvider = new \XCart\SUS\ConnectionProvider(
                \XCart\SUS\Config::$tokenURL,
                ['Cookie' => 'bus_token=' . $_COOKIE['bus_token']]
            );

            $dataReader = new \XCart\SUS\DataReader($connectionProvider, ['content-type' => 'application/json']);
            $code       = $dataReader->getCode();

            $connectionProvider->disconnect();
        } catch (XCart\SUS\Exception $e) {
            throw new \XCart\SUS\Exception($e->getMessage(), $e->getData(), 503, $e);
        }

        switch ($code) {
            case 200:
                if (isset($_GET['version'])
                    && preg_match('/^(\d+)(?:\.?(\d+))?(?:\.?(\d+))?(?:\.?(\d+))?$/', $_GET['version'], $matches)
                ) {
                    $susVersion = implode('.', array_slice($matches, 1));
                }
                break;
            case 401:
                throw new \XCart\SUS\Exception('Unauthorised request', [
                    'bus_token' => $_COOKIE['bus_token'],
                ], 401);

                break;
            default:
                throw new \XCart\SUS\Exception('Service unavailable', [
                    'bus_token'  => $_COOKIE['bus_token'],
                    'code'       => $code,
                    'dataReader' => $dataReader,
                ], 503);

                break;
        }
    } else {

        throw new \XCart\SUS\Exception('Bad request', $_SERVER, 400);
    }


    $connectionProvider = new \XCart\SUS\ConnectionProvider(\XCart\SUS\Config::getPackageURL($susVersion));

    $data = (new \XCart\SUS\DataReader($connectionProvider, ['content-type' => 'application/x-tar']))->getData();

    $connectionProvider->disconnect();

    $package = new \XCart\SUS\Package($data, $fileManger);

    if ($package->check() && $package->extractTo(__DIR__, true)) {
        isset($_GET['emulate']) ?: $package->extractTo(__DIR__);

    } else {

        throw new \XCart\SUS\Exception('Wrong archive received', [
            'check' => $package->getCheckResult(),
        ]);
    }

} catch (\XCart\SUS\Exception $e) {
    XCart\SUS\Logger::log($e->getMessage(), $e->getData() ?: null);

    http_response_code($e->getCode() ?: 500);
}

header('Content-type: application/json');
echo '{}';
