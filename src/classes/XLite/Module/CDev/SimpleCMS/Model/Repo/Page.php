<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\CDev\SimpleCMS\Model\Repo;

/**
 * Pages repository
 *
 */
class Page extends \XLite\Model\Repo\Base\I18n
{
    /**
     * Default 'order by' field name
     *
     * @var string
     */
    protected $defaultOrderBy = 'position';

    /**
     * Alternative record identifiers
     *
     * @var array
     */
    protected $alternativeIdentifier = array(
        array('cleanURL'),
    );

    /**
     * Returns maximal position
     *
     * @return integer
     */
    public function getMaxPosition()
    {
        $qb = $this->createQueryBuilder('page');
        return $qb->select('MAX(page.position)')->getSingleScalarResult();
    }

    // {{{XML Sitemap

    /**
     * Count pages as sitemaps links
     *
     * @return integer
     */
    public function countPagesAsSitemapsLinks()
    {
        return $this->defineCountQuery()
            ->andWhere('p.enabled = true')
            ->count();
    }

    /**
     * Find one as sitemap link
     *
     * @param integer $position Position
     *
     * @return \XLite\Module\CDev\SimpleCMS\Model\Page
     */
    public function  findOneAsSitemapLink($position)
    {
        return $this->createPureQueryBuilder()
            ->andWhere('p.enabled = true')
            ->setMaxResults(1)
            ->setFirstResult($position)
            ->getSingleResult();
    }

    // }}}
}
