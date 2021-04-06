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

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter as BaseFilter;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;

abstract class Filter extends BaseFilter
{
    /**
     * @var bool
     */
    protected $active = false;

    public function apply(BaseProxyQueryInterface $query, array $filterData): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(sprintf('The query MUST implement "%s".', ProxyQueryInterface::class));
        }

        $field = $this->getParentAssociationMappings() ? $this->getName() : $this->getFieldName();

        $this->filter($query, $field, $filterData);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @phpstan-param array{type?: int|null, value?: mixed} $data
     */
    abstract protected function filter(ProxyQueryInterface $query, string $field, array $data): void;
}
