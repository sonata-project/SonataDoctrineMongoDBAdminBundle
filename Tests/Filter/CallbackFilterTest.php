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

use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\CallbackFilter;

class CallbackFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterClosureEmpty()
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => function ($builder, $alias, $field, $value) {
                return true;
            },
        ]);

        $filter->filter($builder, 'alias', 'field', false);
        $filter->filter($builder, 'alias', 'field', 'scalarValue');
        $filter->filter($builder, 'alias', 'field', ['value' => '']);

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilterClosureNotEmpty()
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => function ($builder, $alias, $field, $value) {
                return true;
            },
        ]);

        $filter->filter($builder, 'alias', 'field', ['value' => 'myValue']);

        $this->assertEquals(true, $filter->isActive());
    }

    public function testFilterMethodEmpty()
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->filter($builder, 'alias', 'field', false);
        $filter->filter($builder, 'alias', 'field', 'scalarValue');
        $filter->filter($builder, 'alias', 'field', ['value' => '']);

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilterMethodNotEmpty()
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->filter($builder, 'alias', 'field', ['value' => 'myValue']);

        $this->assertEquals(true, $filter->isActive());
    }

    public function customCallback($builder, $alias, $field, $value)
    {
        return true;
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFilterException()
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', []);

        $filter->filter($builder, 'alias', 'field', 'myValue');
    }
}
