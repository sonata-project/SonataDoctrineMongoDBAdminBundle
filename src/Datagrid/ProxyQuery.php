<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Datagrid;

use Doctrine\ODM\MongoDB\Query\Builder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * This class try to unify the query usage with Doctrine.
 */
class ProxyQuery implements ProxyQueryInterface
{
    protected $queryBuilder;
    protected $sortBy;
    protected $sortOrder;
    protected $firstResult;
    protected $maxResults;

    public function __construct(Builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function __call($name, $args)
    {
        return \call_user_func_array([$this->queryBuilder, $name], $args);
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * @return mixed
     */
    public function execute(array $params = [], $hydrationMode = null)
    {
        // always clone the original queryBuilder.
        $queryBuilder = clone $this->queryBuilder;

        // todo : check how doctrine behave, potential SQL injection here ...
        $sortBy = $this->getSortBy();
        if ($sortBy) {
            $queryBuilder->sort($sortBy, $this->getSortOrder());
        }

        return $queryBuilder->getQuery()->execute($params, $hydrationMode);
    }

    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $this->sortBy = $fieldMapping['fieldName'];
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function getSingleScalarResult()
    {
        $query = $this->queryBuilder->getQuery();

        return $query->getSingleResult();
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;
        $this->queryBuilder->skip($firstResult ?? 0);
    }

    public function getFirstResult()
    {
        return $this->firstResult;
    }

    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        // @see https://docs.mongodb.com/manual/reference/method/cursor.limit/#zero-value
        $this->queryBuilder->limit($maxResults ?? 0);
    }

    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @return mixed
     */
    public function getUniqueParameterId()
    {
        // TODO: Implement getUniqueParameterId() method.
    }

    /**
     * @return mixed
     */
    public function entityJoin(array $associationMappings)
    {
        // TODO: Implement entityJoin() method.
    }
}
