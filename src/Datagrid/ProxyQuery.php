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

/**
 * This class try to unify the query usage with Doctrine.
 *
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class ProxyQuery implements ProxyQueryInterface
{
    /**
     * @var Builder
     */
    protected $queryBuilder;

    /**
     * @var string|null
     */
    protected $sortBy;

    /**
     * @var string
     */
    protected $sortOrder;

    /**
     * @var int|null
     */
    protected $firstResult;

    /**
     * @var int|null
     */
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

    public function execute(array $params = [], $hydrationMode = null)
    {
        if ([] !== $params || null !== $hydrationMode) {
            // NEXT_MAJOR : remove the `trigger_error()` call and uncomment the exception
            @trigger_error(sprintf(
                'Passing a value different than an empty array as argument 1 or "null" as argument 2 for "%s()" is'
                .' deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will throw an exception'
                .' in 4.0. The values provided in this array are not used.',
                __METHOD__
            ), \E_USER_DEPRECATED);

            // throw new \InvalidArgumentException(sprintf(
            //    'No arguments must be passed to "%s()".'
            //    __METHOD__
            // ));
        }

        // always clone the original queryBuilder.
        $queryBuilder = clone $this->queryBuilder;

        // todo : check how doctrine behave, potential SQL injection here ...
        $sortBy = $this->getSortBy();
        if ($sortBy) {
            $queryBuilder->sort($sortBy, $this->getSortOrder());
        }

        return $queryBuilder->getQuery()->execute();
    }

    public function setSortBy($parentAssociationMappings, $fieldMapping)
    {
        $parents = '';

        foreach ($parentAssociationMappings as $mapping) {
            $parents .= $mapping['fieldName'].'.';
        }

        $this->sortBy = $parents.$fieldMapping['fieldName'];

        return $this;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
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

        return $this;
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

        return $this;
    }

    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.5 and will be removed in version 4.0.
     *
     * @return void
     */
    public function getUniqueParameterId()
    {
        // TODO: Implement getUniqueParameterId() method.
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.5 and will be removed in version 4.0.
     *
     * @return void
     */
    public function entityJoin(array $associationMappings)
    {
        // TODO: Implement entityJoin() method.
    }
}
