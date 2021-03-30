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

    public function apply($query, $filterData): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            /* NEXT_MAJOR: Remove this deprecation and uncomment the error */
            @trigger_error(sprintf(
                'Passing %s as argument 1 to %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x'
                .' and will throw a \TypeError error in version 4.0. You MUST pass an instance of %s instead.',
                \get_class($query),
                __METHOD__,
                ProxyQueryInterface::class
            ), \E_USER_DEPRECATED);

            // throw new \TypeError(sprintf('The query MUST implement "%s".', ProxyQueryInterface::class));
        }

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
    abstract protected function filter(BaseProxyQueryInterface $query, string $field, $data): void;
}
