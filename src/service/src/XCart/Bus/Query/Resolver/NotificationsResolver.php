<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Query\Resolver;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use XCart\Bus\Core\Annotations\Resolver;
use XCart\Bus\Query\Data\IDataSource;
use XCart\Bus\Query\Data\NotificationsDataSource;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class NotificationsResolver
{
    /**
     * @var IDataSource
     */
    private $notificationsDataSource;

    /**
     * @param NotificationsDataSource $notificationsDataSource
     */
    public function __construct(NotificationsDataSource $notificationsDataSource)
    {
        $this->notificationsDataSource = $notificationsDataSource;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param             $value
     * @param             $args
     * @param             $context
     * @param ResolveInfo $info
     *
     * @return Deferred
     *
     * @Resolver()
     */
    public function getList($value, $args, $context, ResolveInfo $info)
    {
        $this->notificationsDataSource->loadDeferred();

        return new Deferred(function () {
            return $this->notificationsDataSource->getAll();
        });
    }
}
