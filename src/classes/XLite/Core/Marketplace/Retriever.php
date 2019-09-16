<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Core\Marketplace;

use XLite\Core\GraphQL\ClientFactory;

/**
 * Retriever
 */
class Retriever extends \XLite\Base\Singleton
{
    /**
     * @var \XLite\Core\GraphQL\Client\AClient
     */
    private $client;

    /**
     * @param \XLite\Core\Marketplace\Query      $query
     * @param \XLite\Core\Marketplace\Normalizer $normalizer
     *
     * @return array|null
     */
    public function retrieve($query, Normalizer $normalizer)
    {
        try {
            $client = static::getClient();

            $response = $client->query((string) $query);

            /* @var \XLite\Core\GraphQL\Response $response */
            if ($response->hasErrors()) {
                \XLite\Logger::getInstance()->log(
                    ' request errors:'
                    . PHP_EOL
                    . var_export($response->getErrors(), true)
                );
            }

            return $normalizer->normalize($response->getData());
        } catch (\XLite\Core\GraphQL\Exception\UnexpectedValue $e) {
            \XLite\Logger::getInstance()->log(
                $e->getMessage()
                . PHP_EOL
                . var_export($e->getErrors(), true)
            );
        } catch (\XLite\Core\Exception $e) {
            \XLite\Logger::getInstance()->log(
                $e->getMessage()
            );
        }

        return null;
    }

    /**
     * @return \XLite\Core\GraphQL\Client\AClient
     */
    protected function getClient()
    {
        if (null === $this->client) {
            $this->client = ClientFactory::createWithBusAuth(
                $this->getBusUrl(),
                $this->getAuthUrl(),
                $this->getAuthCode()
            );
        }

        return $this->client;
    }

    protected function getBusUrl()
    {
        return \XLite::getInstance()->getShopURL('service.php?/api');
    }

    protected function getAuthUrl()
    {
        return \XLite::getInstance()->getShopURL('service.php?/auth');
    }

    protected function getAuthCode()
    {
        return \Includes\Utils\ConfigParser::getOptions(['installer_details', 'auth_code']);
    }
}
