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

// NEXT MAJOR: Uncomment next line.
// use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Filter as BaseFilter;

abstract class Filter extends BaseFilter
{
    /**
     * @var bool
     */
    protected $active = false;

    public function apply($query, $value): void
    {
        $this->value = $value;

        $field = $this->getParentAssociationMappings() ? $this->getName() : $this->getFieldName();

        // NEXT_MAJOR: Remove null argument.
        $this->filter($query, null, $field, $value);
    }

    // NEXT_MAJOR: Uncomment this code.
    // abstract protected function filter(ProxyQueryInterface $queryBuilder, string $field, $value);

    public function isActive()
    {
        return $this->active;
    }
}
