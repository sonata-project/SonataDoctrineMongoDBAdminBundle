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

use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Query\Builder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;

/**
 * This class try to unify the query usage with Doctrine.
 */
final class ProxyQuery implements ProxyQueryInterface
{
    private Builder $queryBuilder;

    private ?string $sortBy = null;

    private ?string $sortOrder = null;

    private ?int $firstResult = null;

    private ?int $maxResults = null;

    /**
     * @var mixed[]
     */
    private array $options = [];

    public function __construct(Builder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param mixed[] $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->queryBuilder->$name(...$args);
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * @return Iterator<object>
     */
    public function execute()
    {
        // always clone the original queryBuilder.
        $queryBuilder = clone $this->queryBuilder;

        // todo : check how doctrine behave, potential SQL injection here ...
        $sortBy = $this->getSortBy();
        if (null !== $sortBy) {
            $queryBuilder->sort($sortBy, $this->getSortOrder() ?? 'asc');
        }

        $result = $queryBuilder->getQuery($this->getOptions())->execute();
        \assert($result instanceof Iterator);

        return $result;
    }

    public function setSortBy(array $parentAssociationMappings, array $fieldMapping): BaseProxyQueryInterface
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

    public function getQueryBuilder(): Builder
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

    public function setMaxResults(?int $maxResults): BaseProxyQueryInterface
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

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param mixed[] $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
