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

use Sonata\AdminBundle\Filter\Filter as BaseFilter;

abstract class Filter extends BaseFilter
{
    protected $active = false;

    /**
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value)
    {
        $this->value = $value;

        $field = $this->getParentAssociationMappings() ? $this->getName() : $this->getFieldName();

        $this->filter($queryBuilder, null, $field, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
    }
}
