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
final class ProxyQuery implements ProxyQueryInterface
{
    /**
     * @var Builder
     */
    private $queryBuilder;

    /**
     * @var string|null
     */
    private $sortBy;

    /**
     * @var string
     */
    private $sortOrder;

    /**
     * @var int|null
     */
    private $firstResult;

    /**
     * @var int|null
     */
    private $maxResults;

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

    public function setSortBy($parentAssociationMappings, $fieldMapping): ProxyQueryInterface
    {
        $this->sortBy = $fieldMapping['fieldName'];

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortOrder(string $sortOrder): ProxyQueryInterface
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getSortOrder(): ?string
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

    public function setFirstResult(?int $firstResult): ProxyQueryInterface
    {
        $this->firstResult = $firstResult;
        $this->queryBuilder->skip($firstResult ?? 0);

        return $this;
    }

    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    public function setMaxResults($maxResults): ProxyQueryInterface
    {
        $this->maxResults = $maxResults;

        // @see https://docs.mongodb.com/manual/reference/method/cursor.limit/#zero-value
        $this->queryBuilder->limit($maxResults ?? 0);

        return $this;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }
}
