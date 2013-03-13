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

use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\AdminBundle\Form\Type\BooleanType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;

class BooleanFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty()
    {
        $filter = new BooleanFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');
        $filter->filter($builder, 'alias', 'field', 'test');
        $filter->filter($builder, 'alias', 'field', false);

        $filter->filter($builder, 'alias', 'field', array());
        $filter->filter($builder, 'alias', 'field', array(null, 'test'));

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilterNo()
    {
        $filter = new BooleanFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => null, 'value' => BooleanType::TYPE_NO));

        $this->assertEquals(true, $filter->isActive());
    }

    public function testFilterYes()
    {
        $filter = new BooleanFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => null, 'value' => BooleanType::TYPE_YES));

        $this->assertEquals(true, $filter->isActive());
    }

    public function testFilterArray()
    {
        $filter = new BooleanFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => null, 'value' => array(BooleanType::TYPE_NO)));

        $this->assertEquals(true, $filter->isActive());
    }

}
