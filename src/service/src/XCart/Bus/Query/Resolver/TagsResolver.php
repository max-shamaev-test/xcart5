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
use XCart\Bus\Query\Data\TagsDataSource;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class TagsResolver
{
    /**
     * @var TagsDataSource
     */
    private $tagsDataSource;

    /**
     * @param TagsDataSource $tagsDataSource
     */
    public function __construct(TagsDataSource $tagsDataSource)
    {
        $this->tagsDataSource = $tagsDataSource;
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
    public function getList($value, $args, $context, ResolveInfo $info): Deferred
    {
        $this->tagsDataSource->loadDeferred();

        return new Deferred(function () {
            $tags = $this->tagsDataSource->getAll();
            uasort($tags, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            return $tags;
        });
    }
}
