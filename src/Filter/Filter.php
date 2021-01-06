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

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter as BaseFilter;

abstract class Filter extends BaseFilter
{
    /**
     * @var bool
     */
    protected $active = false;

    public function apply($query, $filterData): void
    {
        // NEXT_MAJOR: Remove next line.
        $this->value = $filterData;

        $field = $this->getParentAssociationMappings() ? $this->getName() : $this->getFieldName();

        $this->filter($query, $field, $filterData);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param mixed $data
     */
    abstract protected function filter(ProxyQueryInterface $query, string $field, $data): void;
}
