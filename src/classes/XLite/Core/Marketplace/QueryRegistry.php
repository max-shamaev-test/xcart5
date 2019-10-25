<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Core\Marketplace;

class QueryRegistry
{
    protected static $queries = [
        'banners'             => <<<GraphQL
            banners {
                image
                module
                url
                section
            }
GraphQL
        ,
        'notifications'       => <<<GraphQL
            notifications {
                type
                module
                image
                title
                description
                link
                date
            }
GraphQL
        ,
        'payment_methods'     => <<<GraphQL
            payment_methods %PARAMS% {
                service_name
                class
                type
                orderby
                countries
                exCountries 
                translations {
                    code
                    name
                    title
                    description
                    altAdminDescription
                }
                added
                enabled
                moduleName
                fromMarketplace
                iconURL
                modulePageURL 
            }
GraphQL
        ,
        'shipping_methods'    => <<<GraphQL
            shipping_methods {
                processor
                carrier
                code
                enabled
                added
                moduleName
                translations {
                    code
                    name
                }
                fromMarketplace
                iconURL
            }
GraphQL
        ,
        'gdpr_modules'        => <<<GraphQL
            gdpr_modules {
                id
                moduleName
                description
                installedDate
            }
GraphQL
        ,
        'marketplace_modules' => <<<GraphQL
            modulesPage %PARAMS% {
                count
                modules {
                    id
                    author
                    name
                    authorName
                    moduleName
                    description
                    installed
                    enabled
                    icon
                    revisionDate
                    price
                    downloads
                    tags {
                        name
                    }
                }
            }
GraphQL
        ,
        'installation_data'   => <<<GraphQL
            installationData {
                installationDate
            }
GraphQL
        ,
        'system_data'         => <<<GraphQL
            systemData {
                dataDate
                wave
            }
GraphQL
        ,
        'core_license'        => <<<GraphQL
            coreLicense {
                keyValue
                id
                moduleName
                expiration
                keyData {
                    editionName
                    expDate
                    prolongKey
                    wave
                    xbProductId
                }
            }
GraphQL
        ,
        'inactive_content'        => <<<GraphQL
            modulesPage %PARAMS% {
                count
                modules {
                    id
                    author
                    name
                    authorName
                    moduleName
                    price
                    installed
                    enabled
                    license
                    purchaseUrl
                    editions
                    xbProductId
                }
            }
GraphQL
        ,
        'waves'               => <<<GraphQL
            waves {
                id
                name
            }
GraphQL
        ,
        'setWave'             => <<<GraphQL
            setWave %PARAMS% {
                id
            }
GraphQL
        ,
        'upgrade_entries'     => <<<GraphQL
          build: upgradeList (type: "build") {
            id
            type
          }
          
          minor: upgradeList (type: "minor") {
            id
            type
          }
          
          major: upgradeList (type: "major") {
            id
            type
          }
          
          core: upgradeList (type: "core") {
            id
            type
          }

          self: upgradeList (type: "self") {
            id
            type
          }
GraphQL
        ,
        'changeSkinState'     => <<<GraphQL
            changeSkinState %PARAMS% {
                id
            }
GraphQL
        ,
        'startRebuild'        => <<<GraphQL
            startRebuild %PARAMS% {
                id
            }
GraphQL
        ,
        'registerLicenseKey'  => <<<GraphQL
            registerLicenseKey %PARAMS% {
                key
                action
                alert {
                    type
                    message
                    params
                }
            }
GraphQL
        ,
    ];

    public static function getQuery($type, $params = null)
    {
        if (isset(static::$queries[$type])) {
            return new Query(static::prepareQuery(static::$queries[$type]), $params);
        }

        return null;
    }

    public static function getMutation($type, $params = null)
    {
        if (isset(static::$queries[$type])) {
            return new Query(static::prepareMutation(static::$queries[$type]), $params);
        }

        return null;
    }

    public static function getComplexQuery(array $types)
    {
        $queries = [];

        foreach ($types as $type) {
            if (isset(static::$queries[$type])) {
                $queries[] = static::$queries[$type];
            }
        }

        if (!empty($queries)) {
            return static::prepareQuery(implode(' ', $queries));
        }

        return null;
    }

    protected static function prepareQuery($query)
    {
        return 'query { ' . $query . ' }';
    }

    protected static function prepareMutation($query)
    {
        return 'mutation { ' . $query . ' }';
    }
}
