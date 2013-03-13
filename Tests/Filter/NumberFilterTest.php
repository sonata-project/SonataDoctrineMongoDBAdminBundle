<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Filter;

use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;

class NumberFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty()
    {
        $filter = new NumberFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', 'asds');

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilterInvalidOperator()
    {
        $filter = new NumberFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => 'foo'));

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilter()
    {
        $filter = new NumberFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_EQUAL, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_GREATER_EQUAL, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_GREATER_THAN, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_LESS_EQUAL, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('type' => NumberType::TYPE_LESS_THAN, 'value' => 42));
        $filter->filter($builder, 'alias', 'field', array('value' => 42));

        $this->assertEquals(true, $filter->isActive());
    }
}
