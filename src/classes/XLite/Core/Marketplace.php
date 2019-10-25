<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Core;

use XCart\Marketplace\Constant;
use XCart\MarketplaceShop;
use XLite\Core\Cache\ExecuteCached;

/**
 * Marketplace
 */
class Marketplace extends \XLite\Base\Singleton
{
    /**
     * Dedicated return code for the "performActionWithTTL" method
     */
    const TTL_NOT_EXPIRED = '____TTL_NOT_EXPIRED____';

    /**
     * Some predefined TTLs
     */
    const TTL_LONG  = 86400;
    const TTL_SHORT = 3600;

    /**
     * HTTP request TTL for 'test_marketplace' action
     */
    const TTL_TEST_MP = 300; // 5 minutes

    /**
     * PurchaseURL host
     */
    const PURCHASE_URL_HOST = 'market.x-cart.com';

    /**
     * Last error code
     *
     * @var string
     */
    protected static $lastErrorCode;

    /**
     * Error message
     *
     * @var mixed
     */
    protected $error;

    protected $systemData;

    /**
     * @return string
     */
    public static function getBusinessPurchaseURL()
    {
        $marketplaceShop = static::buildMarketplaceShop();

        return $marketplaceShop->getPurchaseURL();
    }

    /**
     * @param int   $id
     * @param array $params
     * @param bool  $ignoreId
     *
     * @return string
     */
    public static function getPurchaseURL($id = 0, array $params = [], $ignoreId = false)
    {
        $marketplaceShop = static::buildMarketplaceShop();

        return $marketplaceShop->getPurchaseURL($id, $params, $ignoreId);
    }

    /**
     * @return MarketplaceShop
     */
    public static function buildMarketplaceShop()
    {
        $adminEmail = \XLite\Core\Auth::getInstance()->isAdmin()
            ? \XLite\Core\Auth::getInstance()->getProfile()->getLogin()
            : null;

        $licenseKeyMD5 = '';
        $licenseKey    = \XLite::getXCNLicenseKey();
        if ($licenseKey) {
            $licenseKeyMD5 = md5($licenseKey);
        }

        $controller = '';
        if (\XLite::isAdminZone() && \XLite::getController()) {
            $controller = \XLite::getController()->getTarget();
        }

        return MarketplaceShop::build(
            \XLite\Core\URLManager::getShopURL(\XLite\Core\Converter::buildURL()),
            $adminEmail,
            $licenseKeyMD5,
            \XLite::getAffiliateId(),
            $controller,
            \XLite::getInstallationLng()
        );
    }

    /**
     * This function defines original link to X-Cart.com site's Contact Us page
     *
     * @return string
     */
    public static function getContactUsURL()
    {
        return \XLite::getXCartURL('https://www.x-cart.com/contact-us.html');
    }

    /**
     * This function defines original link to X-Cart.com site's License Agreement page
     *
     * @return string
     */
    public static function getLicenseAgreementURL()
    {
        return \XLite::getXCartURL('https://www.x-cart.com/license-agreement.html');
    }

    /**
     * @return array
     */
    public function getSystemData()
    {
        if (!$this->systemData) {
            $path = LC_DIR_FILES . 'service' . LC_DS . 'coreConfigStorage.data';
            $content = \Includes\Utils\FileManager::read($path);
            $systemData = @unserialize($content, ['allowed_classes' => false]);

            $this->systemData = $systemData ?: [];
        }

        return $this->systemData;
    }

    /**
     * @param array $systemData
     */
    public function setSystemData($systemData)
    {
        $content = @serialize($systemData);

        $path = LC_DIR_FILES . 'service' . LC_DS . 'coreConfigStorage.data';

        \Includes\Utils\FileManager::write($path, $content);
    }

    public function setFreshInstall()
    {
        $systemData = $this->getSystemData();
        $systemData['freshInstall'] = true;

        $this->setSystemData($systemData);
    }

    /**
     * Check if cache was reset by service.php?/clear-cache
     *
     * @param $cell
     * @param $serviceVar
     *
     * @return bool
     */
    protected function isServiceCacheReset($cell, $serviceVar)
    {
        $start = \XLite\Core\TmpVars::getInstance()->$cell;
        $systemData = $this->getSystemData();

        return isset($start)
            && isset($systemData[$serviceVar])
            && $start < $systemData[$serviceVar];
    }

    /**
     * @return array
     */
    public function getPaymentMethods(string $countryCode = '')
    {
        return Marketplace\Retriever::getInstance()->retrieve(
            Marketplace\QueryRegistry::getQuery('payment_methods', ['countryCode' => $countryCode]),
            new Marketplace\Normalizer\PaymentMethods()
        );
    }

    /**
     * Update payment methods
     *
     * @param integer|null $ttl TTL
     */
    public function updatePaymentMethods($countryCode, $ttl = null)
    {
        $countryCode = $countryCode ?: \XLite\Core\Config::getInstance()->Company->location_country;
        list($cellTTL,) = $this->getActionCacheVars(Constant::REQUEST_PAYMENT_METHODS . '-' . $countryCode . '-');

        $ttl = $ttl ?? static::TTL_LONG;

        // Check if expired
        if (!$this->checkTTL($cellTTL, $ttl) || $this->isServiceCacheReset($cellTTL, 'paymentMethodsCacheDate')) {
            if ($data = $this->getPaymentMethods($countryCode)) {
                \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->updatePaymentMethods($data, $countryCode);
                $this->setTTLStart($cellTTL);
            }
        }
    }

    /**
     * @return array
     */
    public function getShippingMethods()
    {
        return Marketplace\Retriever::getInstance()->retrieve(
            Marketplace\QueryRegistry::getQuery('shipping_methods'),
            new Marketplace\Normalizer\ShippingMethods()
        );
    }

    /**
     * Update shipping methods
     *
     * @param integer|null $ttl TTL
     */
    public function updateShippingMethods($ttl = null)
    {
        list($cellTTL,) = $this->getActionCacheVars(Constant::REQUEST_SHIPPING_METHODS);

        $ttl = $ttl ?? static::TTL_LONG;

        // Check if expired
        if (!$this->checkTTL($cellTTL, $ttl) || $this->isServiceCacheReset($cellTTL, 'shippingMethodsCacheDate')) {
            if ($data = $this->getShippingMethods()) {
                \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->updateShippingMethods($data);
                $this->setTTLStart($cellTTL);
            }
        }
    }

    // {{{ "Get dataset" request

    /**
     * Get actions list for 'get_dataset' request
     *
     * @return array
     */
    public function getActionsForGetDataset()
    {
        $actions = array_fill_keys($this->getExpiredActions(), []);

        $scheduled = $this->getScheduledActions();

        if ($scheduled) {
            $actions = array_merge($actions, $scheduled);
        }

        return array_map([$this, 'mapRequestNameToType'], array_keys($actions));
    }

    /**
     * Return true if action is active (non-empty and not expired)
     *
     * @param string $action Action type
     *
     * @return boolean
     */
    public function isActionActive($action)
    {
        list($cellTTL,) = $this->getActionCacheVars($action);

        return !$this->checkTTL($cellTTL, $this->getActionTTL($action));
    }

    /**
     * Get installation data
     *
     * @return array
     */
    public function getInstallationData()
    {
        try {
            return $this->performRequestWithCache(
                Constant::REQUEST_INSTALLATION_DATA,
                function () {
                    $result = Marketplace\Retriever::getInstance()->retrieve(
                        Marketplace\QueryRegistry::getQuery('installation_data'),
                        new Marketplace\Normalizer\InstallationData()
                    );

                    if ($result === null) {
                        throw new \Exception();
                    }

                    return $result;
                }
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get installation data
     *
     * @return array
     */
    public function getCoreLicense()
    {
        $systemData = $this->getSystemData();

        $result = $this->performRequestWithCache(
            [Constant::REQUEST_CORE_LICENSE, $systemData['dataDate'] ?? 0],
            function () {
                return Marketplace\Retriever::getInstance()->retrieve(
                    Marketplace\QueryRegistry::getQuery('core_license'),
                    new Marketplace\Normalizer\CoreLicense()
                );
            }
        );

        return $result;
    }

    public function hasUnallowedModules($onlyEnabled = true)
    {
        $inactiveModules = $this->getInactiveContentData();

        if (!$onlyEnabled) {
            return 0 < count($inactiveModules);
        }

        foreach ($inactiveModules as $module) {
            if (isset($module['enabled']) && true === $module['enabled']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get installation data
     *
     * @return array
     */
    public function getWaves()
    {
        return Marketplace\Retriever::getInstance()->retrieve(
            Marketplace\QueryRegistry::getQuery('waves'),
            new Marketplace\Normalizer\Waves()
        );
    }

    /**
     * @param string $wave
     *
     * @return array
     */
    public function setWave($wave)
    {
        return \XLite\Core\Marketplace\Retriever::getInstance()->retrieve(
            \XLite\Core\Marketplace\QueryRegistry::getMutation('setWave', [
                'wave' => $wave
            ]),
            new \XLite\Core\Marketplace\Normalizer\Raw()
        ) ?: [];
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function registerLicense($key)
    {
        return \XLite\Core\Marketplace\Retriever::getInstance()->retrieve(
            \XLite\Core\Marketplace\QueryRegistry::getMutation('registerLicenseKey', [
                'key' => $key
            ]),
            new \XLite\Core\Marketplace\Normalizer\RegisterLicenseKey()
        ) ?: [];
    }

    /**
     * The certain request handler
     *
     * @return array
     */
    public function getAllBanners()
    {
        return $this->performRequestWithCache(
            Constant::REQUEST_BANNERS,
            function () {
                return Marketplace\Retriever::getInstance()->retrieve(
                    Marketplace\QueryRegistry::getQuery('banners'),
                    new Marketplace\Normalizer\Banners()
                );
            }
        );
    }

    // }}}

    /**
     * @return array
     */
    public function getAccountingModules()
    {
        $criteria = [
            'tag'       => 'Accounting',
            'installed' => true,
        ];

        $systemData = $this->getSystemData();

        $cacheKeyData = $criteria + [
                'dataDate' => $systemData['dataDate'] ?? 0,
            ];

        return $this->performRequestWithCache($cacheKeyData, function () use ($criteria) {
            return \XLite\Core\Marketplace\Retriever::getInstance()->retrieve(
                \XLite\Core\Marketplace\QueryRegistry::getQuery('marketplace_modules', $criteria),
                new \XLite\Core\Marketplace\Normalizer\MarketplaceModules()
            ) ?: [];
        });
    }

    /**
     * @param $modules
     *
     * @return mixed
     */
    public function getAutomateShippingRoutineModules($modules)
    {
        $criteria = [
            'includeIds' => $modules,
        ];

        $systemData = $this->getSystemData();

        $cacheKeyData = $criteria + [
                'dataDate' => $systemData['dataDate'] ?? 0,
            ];

        return $this->performRequestWithCache($cacheKeyData, function () use ($criteria) {
            return \XLite\Core\Marketplace\Retriever::getInstance()->retrieve(
                \XLite\Core\Marketplace\QueryRegistry::getQuery('marketplace_modules', $criteria),
                new \XLite\Core\Marketplace\Normalizer\MarketplaceModules()
            ) ?: [];
        });
    }

    /**
     * The certain request handler
     *
     * @return array
     */
    public function getXC5Notifications()
    {
        $result = $this->performActionRequestWithCache(
            Constant::REQUEST_NOTIFICATIONS,
            function () {
                return Marketplace\Retriever::getInstance()->retrieve(
                    Marketplace\QueryRegistry::getQuery('notifications'),
                    new Marketplace\Normalizer\Notifications()
                );
            }
        );

        return $result ?: [];
    }

    /**
     * Unseen updates available hash
     *
     * @return string Hash of modules updates messages
     */
    public function unseenUpdatesHash()
    {
        $result   = [];
        $messages = $this->getXC5Notifications();
        // $coreVersion = \XLite\Upgrade\Cell::getInstance()->getCoreVersion();
        // todo: use some other way through the BUS
        $coreVersion = '';// \XLite\Upgrade\Cell::getInstance()->getCoreVersion();
        if ($messages) {
            foreach ($messages as $message) {
                if ($message['type'] === 'module') {
                    $result[] = $message;
                }
            }
        }

        return md5(serialize($result) . serialize($coreVersion));
    }

    /**
     * Return true if system detected inactive license key
     *
     * @return array
     */
    public function getInactiveContentData()
    {
        $systemData = $this->getSystemData();

        $result = $this->performRequestWithCache(
            [Constant::REQUEST_OUTDATED_MODULE, $systemData['dataDate'] ?? 0],
            function () {
                return Marketplace\Retriever::getInstance()->retrieve(
                    Marketplace\QueryRegistry::getQuery('inactive_content', ['licensed' => false]),
                    new Marketplace\Normalizer\MarketplaceModules()
                );
            }
        );

        return $result ?: [];
    }

    // {{{ "Get xc5 notifications" request

    /**
     * Get list of expired actions
     *
     * @return array
     */
    protected function getExpiredActions()
    {
        return array_filter(array_keys($this->getMarketplaceActions()), [$this, 'isActionActive']);
    }

    /**
     * Get list of actions which cannot be issued in the 'get_dataset' request
     *
     * @return array
     */
    protected function getDatasetExcludedActions()
    {
        return [
            Constant::REQUEST_ADDON_HASH,
            Constant::REQUEST_ADDON_INFO,
            Constant::REQUEST_ADDON_PACK,
            Constant::REQUEST_CORE_HASH,
            Constant::REQUEST_CORE_PACK,
            Constant::REQUEST_OUTDATED_MODULE,
            Constant::REQUEST_PAYMENT_METHODS,
            Constant::REQUEST_RESEND_KEY,
            Constant::REQUEST_SET,
            Constant::REQUEST_SET_KEY_WAVE,
            Constant::REQUEST_SHIPPING_METHODS,
            Constant::REQUEST_TEST,
        ];
    }

    // }}}

    // {{{ Cache-related routines

    /**
     * Process result of 'get_dataset' request
     *
     * @param array $responseData Result data of 'get_dataset' request
     *
     * @return array
     */
    protected function processGetDatasetResult($responseData)
    {
        if (is_array($responseData)) {
            foreach ($responseData as $action => $data) {
                // $result is NULL when nothing is received from the marketplace
                if (is_array($data)) {
                    $saveInTmpVars = true;

                    $this->saveResultInCache($action, $data, $saveInTmpVars);
                }
            }
        }

        return $responseData;
    }

    protected function performRequestWithCache($cacheParams, callable $callback, $ttl = 86400)
    {
        return ExecuteCached::executeCached(
            $callback,
            $cacheParams,
            $ttl
        );
    }

    protected function performActionRequestWithCache($requestName, callable $callback)
    {
        \XLite\Core\Lock\MarketplaceLocker::getInstance()->waitForUnlocked($requestName);
        \XLite\Core\Lock\MarketplaceLocker::getInstance()->lock($requestName);

        if (!$this->isActionActive($requestName)) {
            list(, $dataCell) = $this->getActionCacheVars($requestName);
            $result = \XLite\Core\TmpVars::getInstance()->$dataCell;

            $this->scheduleAction($requestName, []);
        } else {
            $result = $callback();
            $this->saveResultInCache($requestName, $result, true);
        }

        \XLite\Core\Lock\MarketplaceLocker::getInstance()->unlock($requestName);

        return $result;
    }

    protected function getRequestTypeToNameAssociations()
    {
        return [
            'banners'          => Constant::REQUEST_BANNERS,
            'notifications'    => Constant::REQUEST_NOTIFICATIONS,
        ];
    }

    protected function mapRequestTypeToName($type)
    {
        return isset($this->getRequestTypeToNameAssociations()[$type])
            ? $this->getRequestTypeToNameAssociations()[$type]
            : null;
    }

    protected function mapRequestNameToType($name)
    {
        return ($k = array_search($name, $this->getRequestTypeToNameAssociations(), true)) !== false
            ? $k
            : null;
    }

    protected function saveRequestInCache($requestType, $data)
    {
        if ($requestName = $this->mapRequestTypeToName($requestType)) {
            $this->saveResultInCache($requestName, $data, true);

            return true;
        }

        return false;
    }

    /**
     * Return list of marketplace request types which are cached in tmp_vars
     *
     * @return array
     */
    protected function getCachedRequestTypes()
    {
        return [
            Constant::REQUEST_UPDATES,
            Constant::REQUEST_CHECK_ADDON_KEY,
            Constant::REQUEST_CORES,
            Constant::REQUEST_ADDONS,
            Constant::REQUEST_BANNERS,
            Constant::REQUEST_TAGS,
            //    Constant::ACTION_GET_HOSTING_SCORE,
            Constant::REQUEST_LANDING,
            Constant::REQUEST_WAVES,
            Constant::INACTIVE_KEYS,
            Constant::REQUEST_PAYMENT_METHODS,
            Constant::REQUEST_SHIPPING_METHODS,
            Constant::REQUEST_NOTIFICATIONS,
        ];
    }

    /**
     * Get all marketplace actions list
     *
     * @return array
     */
    protected function getMarketplaceActions()
    {
        return [
            Constant::REQUEST_UPDATES          => static::TTL_LONG,
            Constant::REQUEST_CHECK_ADDON_KEY  => static::TTL_LONG,
            Constant::REQUEST_CORES            => static::TTL_LONG,
            Constant::REQUEST_ADDONS           => static::TTL_LONG,
            Constant::REQUEST_BANNERS          => static::TTL_LONG,
            Constant::REQUEST_TAGS             => static::TTL_LONG,
            //  Constant::ACTION_GET_HOSTING_SCORE     => static::TTL_LONG,
            Constant::REQUEST_LANDING          => static::TTL_LONG,
            Constant::REQUEST_WAVES            => static::TTL_LONG,
            Constant::REQUEST_NOTIFICATIONS    => static::TTL_SHORT,
            Constant::REQUEST_PAYMENT_METHODS  => static::TTL_LONG,
            Constant::REQUEST_SHIPPING_METHODS => static::TTL_LONG,

            Constant::REQUEST_INSTALLATION_DATA => static::TTL_NOT_EXPIRED,
        ];
    }

    /**
     * Get action TTL
     *
     * @param string $action Action type
     *
     * @return integer
     */
    protected function getActionTTL($action)
    {
        $ttls = $this->getMarketplaceActions();

        return isset($ttls[$action]) ? $ttls[$action] : null;
    }

    /**
     * Return action cache variables
     *
     * @param string $action Marketplace action
     *
     * @return array
     */
    protected function getActionCacheVars($action)
    {
        return [
            $action . 'TTL',
            $action . 'Data',
        ];
    }

    /**
     * Save result in the cache
     *
     * @param string  $action        Action type
     * @param mixed   $result        Result
     * @param boolean $saveInTmpVars Flag: true - save result in cache, false - save only timestamp or request
     */
    protected function saveResultInCache($action, $result, $saveInTmpVars)
    {
        list($cellTTL, $cellData) = $this->getActionCacheVars($action);

        if ($saveInTmpVars) {
            // Save in DB (if needed)
            \XLite\Core\TmpVars::getInstance()->$cellData = $result;
        }

        $this->removeScheduledAction($action);
        $this->setTTLStart($cellTTL);
    }

    /**
     * Schedule action
     *
     * @param string $action Action type
     * @param array  $data   Action data
     *
     * @return void
     */
    protected function scheduleAction($action, $data)
    {
        $current = \XLite\Core\TmpVars::getInstance()->marketplaceSchedule;

        if (!$current) {
            $current = [];
        }

        $current[$action] = $data;

        \XLite\Core\TmpVars::getInstance()->marketplaceSchedule = $current;
    }

    /**
     * Remove action from the scheduled actions list
     *
     * @param string $action Action type
     *
     * @return void
     */
    protected function removeScheduledAction($action)
    {
        $current = \XLite\Core\TmpVars::getInstance()->marketplaceSchedule;

        if (isset($current[$action])) {
            unset($current[$action]);
            \XLite\Core\TmpVars::getInstance()->marketplaceSchedule = $current ?: null;
        }

    }

    /**
     * Get list of scheduled actions
     *
     * @return array
     */
    protected function getScheduledActions()
    {
        return \XLite\Core\TmpVars::getInstance()->marketplaceSchedule;
    }

    /**
     * Clear list of scheduled actions
     *
     * @return void
     */
    protected function clearScheduledActions()
    {
        \XLite\Core\TmpVars::getInstance()->marketplaceSchedule = null;
    }

    /**
     * Check and update cache TTL
     *
     * @param string  $cell Name of the cache cell
     * @param integer $ttl  TTL value (in seconds)
     *
     * @return boolean
     */
    protected function checkTTL($cell, $ttl)
    {
        if ($ttl === static::TTL_NOT_EXPIRED) {
            return true;
        }

        // Fetch a certain cell value
        $start = \XLite\Core\TmpVars::getInstance()->$cell;

        return isset($start) && \XLite\Core\Converter::time() < ($start + $ttl);
    }

    // }}}

    // {{{ License check

    /**
     * Renew TTL cell value
     *
     * @param string $cell Name of the cache cell
     *
     * @return void
     */
    protected function setTTLStart($cell)
    {
        \XLite\Core\TmpVars::getInstance()->$cell = \XLite\Core\Converter::time();
    }

    // }}}
}
