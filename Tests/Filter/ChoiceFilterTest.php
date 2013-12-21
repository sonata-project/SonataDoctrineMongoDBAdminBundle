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

use Sonata\DoctrineMongoDBAdminBundle\Filter\ChoiceFilter;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;

class ChoiceFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty()
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', 'all');
        $filter->filter($builder, 'alias', 'field', array());

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilterArray()
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => ChoiceType::TYPE_CONTAINS, 'value' => array('1', '2')));

        $this->assertEquals(true, $filter->isActive());
    }

    public function testFilterScalar()
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => ChoiceType::TYPE_CONTAINS, 'value' => '1'));

        $this->assertEquals(true, $filter->isActive());
    }

    public function testFilterZero()
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => ChoiceType::TYPE_CONTAINS, 'value' => 0));

        $this->assertEquals(true, $filter->isActive());
    }
}
