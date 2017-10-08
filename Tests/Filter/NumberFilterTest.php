<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Filter;

use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;

class NumberFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty()
    {
        $filter = new NumberFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', 'asds');

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilterInvalidOperator()
    {
        $filter = new NumberFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', ['type' => 'foo']);

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilter()
    {
        $filter = new NumberFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', ['type' => NumberType::TYPE_EQUAL, 'value' => 42]);
        $filter->filter($builder, 'alias', 'field', ['type' => NumberType::TYPE_GREATER_EQUAL, 'value' => 42]);
        $filter->filter($builder, 'alias', 'field', ['type' => NumberType::TYPE_GREATER_THAN, 'value' => 42]);
        $filter->filter($builder, 'alias', 'field', ['type' => NumberType::TYPE_LESS_EQUAL, 'value' => 42]);
        $filter->filter($builder, 'alias', 'field', ['type' => NumberType::TYPE_LESS_THAN, 'value' => 42]);
        $filter->filter($builder, 'alias', 'field', ['value' => 42]);

        $this->assertEquals(true, $filter->isActive());
    }
}
