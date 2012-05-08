<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Datagrid;

use Doctrine\ODM\MongoDB\Query;
use Doctrine\ODM\MongoDB\Query\Builder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * This class try to unify the query usage with Doctrine
 */
class ProxyQuery implements ProxyQueryInterface
{
    protected $queryBuilder;
    protected $sortBy;
    protected $sortOrder;
    protected $firstResult;
    protected $maxResults;

    /**
     * @param \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder
     */
    public function __construct(Builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param array $params
     * @param null $hydrationMode
     * @return mixed
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        // always clone the original queryBuilder
        $queryBuilder = clone $this->queryBuilder;

        // todo : check how doctrine behave, potential SQL injection here ...
        $sortBy = $this->getSortBy();
        if ($sortBy) {
            $queryBuilder->sort($sortBy, $this->getSortOrder());
        }

        return $queryBuilder->getQuery()->execute($params, $hydrationMode);
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->queryBuilder, $name), $args);
    }

    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $alias        = $this->entityJoin($parentAssociationMappings);
        $this->sortBy = $alias . '.' . $fieldMapping['fieldName'];
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

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;
        $this->queryBuilder->skip($firstResult);
    }

    function getFirstResult()
    {
        return $this->firstResult;
    }

    function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        $this->queryBuilder->limit($maxResults);
    }

    function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @return mixed
     */
    function getUniqueParameterId()
    {
        // TODO: Implement getUniqueParameterId() method.
    }

    /**
     * @param array $associationMappings
     *
     * @return mixed
     */
    function entityJoin(array $associationMappings)
    {
        // TODO: Implement entityJoin() method.
    }

}
