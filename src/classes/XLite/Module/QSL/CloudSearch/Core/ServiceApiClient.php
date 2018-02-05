<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\QSL\CloudSearch\Core;

use Includes\Utils\ConfigParser;
use Includes\Utils\URLManager;
use XLite\Core\Config;
use XLite\Core\Converter;
use XLite\Core\Database;
use XLite\Core\HTTP\Request;
use XLite\Module\QSL\CloudSearch\Main;


/**
 * CloudSearch API client
 */
class ServiceApiClient
{
    /**
     * CloudSearch service access details
     */
    const CLOUD_SEARCH_URL = 'https://cloudsearch.x-cart.com';
    const CLOUD_SEARCH_REQUEST_SECRET_KEY_URL = '/api/v1/getkey';
    const CLOUD_SEARCH_REMOTE_IFRAME_URL = '/api/v1/iframe?key=';
    const CLOUD_SEARCH_REGISTER_URL = '/api/v1/register';
    const CLOUD_SEARCH_SEARCH_URL = '/api/v1/search';
    const CLOUD_SEARCH_PLAN_INFO_URL = '/api/v1/plan-info';

    const SEARCH_REQUEST_TIMEOUT = 5;
    const PLAN_INFO_REQUEST_TIMEOUT = 3;

    /**
     * Register CloudSearch installation
     *
     * @return void
     */
    public function register()
    {
        $requestUrl = static::CLOUD_SEARCH_URL . static::CLOUD_SEARCH_REGISTER_URL;

        $shopUrl = $this->getShopUrl();

        $request       = new Request($requestUrl);
        $request->body = [
            'shopUrl'  => $shopUrl,
            'shopType' => 'xc5',
        ];

        $response = $request->sendRequest();

        if ($response && $response->code == 200) {
            $data = json_decode($response->body, true);

            if ($data && !empty($data['apiKey'])) {
                $this->storeApiKey($data['apiKey']);

                Config::updateInstance();

                $this->requestSecretKey();

                Config::updateInstance();
            }
        }
    }

    /**
     * Search functionality on the product list
     *
     * @param array $params
     *
     * @return array
     */
    public function search(array $params)
    {
        $response = $this->performSearchRequest($params);

        return $response && $response->code == 200
            ? $this->extractSearchResultsFromResponse($response)
            : null;
    }

    /**
     * Get search api endpoint url
     *
     * @return string
     */
    public function getSearchApiUrl()
    {
        return static::CLOUD_SEARCH_URL . static::CLOUD_SEARCH_SEARCH_URL;
    }

    /**
     * Get CloudSearch API key
     *
     * @return mixed
     */
    public function getApiKey()
    {
        return Config::getInstance()->QSL->CloudSearch->api_key;
    }

    /**
     * Get CloudSearch API key
     *
     * @return mixed
     */
    public function getSecretKey()
    {
        return Config::getInstance()->QSL->CloudSearch->secret_key;
    }

    /**
     * Retrieve search results from the response body
     *
     * @param \PEAR2\HTTP\Request\Response $response Response object
     *
     * @return array
     */
    protected function extractSearchResultsFromResponse($response)
    {
        $input = json_decode($response->body, true);

        $products = $input
        && $input['products']
        && count($input['products']) > 0 ? $input['products'] : [];

        $facets = $input && isset($input['facets']) ? $input['facets'] : null;
        $stats  = $input && isset($input['stats']) ? $input['stats'] : null;

        return [
            'products'         => $products,
            'numFoundProducts' => $input['numFoundProducts'],
            'facets'           => $facets,
            'stats'            => $stats,
        ];
    }

    /**
     * Perform product search request (ALL) into the CloudSearch service
     *
     * @param array $params
     *
     * @return \PEAR2\HTTP\Request\Response
     */
    protected function performSearchRequest(array $params)
    {
        $request = new Request($this->getSearchApiUrl());

        $request->setAdditionalOption(\CURLOPT_TIMEOUT, self::SEARCH_REQUEST_TIMEOUT);

        $data = [
                'apiKey' => $this->getApiKey(),
                'all'    => 1,
            ] + $params;

        $request->body = json_encode($data);
        $request->verb = 'POST';
        $request->setHeader('Content-Type', 'application/json');

        return $request->sendRequest();
    }

    /**
     * Ask CloudSearch server to send us a new secret key
     *
     * @return void
     */
    public function requestSecretKey()
    {
        $apiKey = $this->getApiKey();

        $requestUrl = static::CLOUD_SEARCH_URL . static::CLOUD_SEARCH_REQUEST_SECRET_KEY_URL;

        $request       = new Request($requestUrl);
        $request->body = [
            'apiKey' => $apiKey,
        ];

        $request->sendRequest();
    }

    /**
     * Request CS plan info
     *
     * @return mixed|null
     */
    public function getPlanInfo()
    {
        $apiKey    = $this->getApiKey();
        $secretKey = $this->getSecretKey();

        $requestUrl = static::CLOUD_SEARCH_URL . static::CLOUD_SEARCH_PLAN_INFO_URL;

        $request = new Request($requestUrl);

        $request->setAdditionalOption(\CURLOPT_TIMEOUT, self::PLAN_INFO_REQUEST_TIMEOUT);

        $request->body = [
            'apiKey'    => $apiKey,
            'secretKey' => $secretKey,
        ];

        $response = $request->sendRequest();

        return $response && $response->code == 200 ? json_decode($response->body, true) : null;
    }

    /**
     * Get CloudSearch dashboard url
     *
     * @param $secretKey
     * @param $params
     *
     * @return string
     */
    public function getDashboardIframeUrl($secretKey, $params)
    {
        $features = ['cloud_filters'];

        return static::CLOUD_SEARCH_URL
            . static::CLOUD_SEARCH_REMOTE_IFRAME_URL
            . $secretKey
            . '&' . http_build_query($params + ['client_features' => $features]);
    }

    /**
     * Get store url without script part
     *
     * @return string
     */
    private function getShopUrl()
    {
        $url = preg_replace(
            '/[^\/]*.php$/',
            '',
            URLManager::getShopURL(Converter::buildURL())
        );

        $protocol = URLManager::isHTTPS() ? 'https' : 'http';

        $hostDetails = ConfigParser::getOptions('host_details');

        if (Main::isMultiDomain() && isset($hostDetails[$protocol . '_host_orig'])) {
            $original_host = $hostDetails[$protocol . '_host_orig'];

            $scheme = parse_url($url, PHP_URL_SCHEME);
            $host   = parse_url($url, PHP_URL_HOST);

            $url = $scheme . '://' . $original_host
                . substr($url, strlen($scheme) + strlen('://') + strlen($host));
        }

        return $url;
    }

    /**
     * Store API key in the DB
     *
     * @param $key
     *
     * @return void
     */
    protected function storeApiKey($key)
    {
        $repo = Database::getRepo('XLite\Model\Config');

        $apiKeySetting = $repo->findOneBy([
            'name'     => 'api_key',
            'category' => 'QSL\CloudSearch',
        ]);

        $apiKeySetting->setValue($key);

        Database::getEM()->flush();
    }
}
