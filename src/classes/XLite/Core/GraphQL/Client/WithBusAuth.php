<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Core\GraphQL\Client;

use GuzzleHttp\Cookie\CookieJar;
use XLite\Core\GraphQL\Exception\UnableToAuthorize;
use GuzzleHttp\Exception\TransferException;


/**
 * GraphQL client with BUS - like authentication
 */
class WithBusAuth extends Simple
{
    const BUS_TOKEN = 'bus_token';

    /**
     * @var string
     */
    private $token;

    /**
     * @var \GuzzleHttp\Client
     */
    private $authClient;

    /**
     * @var string
     */
    private $authCode;

    /**
     * WithBusAuth constructor.
     *
     * @param \GuzzleHttp\Client                  $httpClient
     * @param \XLite\Core\GraphQL\ResponseBuilder $responseBuilder
     * @param \GuzzleHttp\Client                  $authClient
     * @param string                              $authCode
     */
    public function __construct($httpClient, $responseBuilder, $authClient, $authCode)
    {
        parent::__construct($httpClient, $responseBuilder);

        $this->authClient = $authClient;
        $this->authCode = $authCode;
    }

    protected function prepareOptions(array $options)
    {
        $options['cookies'][static::BUS_TOKEN] = $this->getToken();

        return parent::prepareOptions($options);
    }


    /**
     * @return string
     */
    protected function getToken()
    {
        if (is_null($this->token)) {
            $this->token = $this->retrieveToken();
        }

        return $this->token;
    }

    /**
     * Auth request
     * @return string
     * @throws \XLite\Core\Exception
     */
    private function retrieveToken()
    {
        try {
            $request = $this->authClient->createRequest('POST', null, [
                'body' => [
                    'auth_code' => $this->authCode,
                ],
            ]);
            $response = $this->authClient->send($request);

            if ($response->getStatusCode() !== 200) {
                throw new \GuzzleHttp\Exception\BadResponseException(
                    "GraphQL authorization request failed: expected HTTP code \"200\" received \"{$response->getStatusCode()}\"",
                    $this->authClient->createRequest('POST'),
                    $response
                );
            }

            return $this->retrieveBusTokenFromResponse($request, $response);
        } catch (TransferException $e) {
            throw new \XLite\Core\Exception(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Parse response for BUS token
     *
     * @param \GuzzleHttp\Message\RequestInterface  $request
     * @param \GuzzleHttp\Message\ResponseInterface $response
     *
     * @return mixed
     * @throws \XLite\Core\GraphQL\Exception\UnableToAuthorize
     */
    private function retrieveBusTokenFromResponse($request, $response)
    {
        $jar = new CookieJar();
        $jar->extractCookies($request, $response);

        /* @var \GuzzleHttp\Cookie\SetCookie $item */
        foreach ($jar->getIterator() as $item) {
            if ($item->getName() === static::BUS_TOKEN) {
                return $item->getValue();
            }
        }

        throw new UnableToAuthorize('GraphQL authorization request failed: BUS token cookie not found');
    }
}