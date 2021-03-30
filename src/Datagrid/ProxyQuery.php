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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;

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
            throw new \InvalidArgumentException(sprintf(
                'No arguments must be passed to "%s()".',
                __METHOD__
            ));
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

    public function setSortBy($parentAssociationMappings, $fieldMapping): BaseProxyQueryInterface
    {
        $parents = '';

        foreach ($parentAssociationMappings as $mapping) {
            $parents .= $mapping['fieldName'].'.';
        }

        $this->sortBy = $parents.$fieldMapping['fieldName'];

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortOrder(string $sortOrder): BaseProxyQueryInterface
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

    public function setFirstResult(?int $firstResult): BaseProxyQueryInterface
    {
        $this->firstResult = $firstResult;
        $this->queryBuilder->skip($firstResult ?? 0);

        return $this;
    }

    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    public function setMaxResults($maxResults): BaseProxyQueryInterface
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
