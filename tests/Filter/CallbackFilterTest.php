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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Filter;

use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\CallbackFilter;

class CallbackFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterClosureEmpty(): void
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

        $this->assertFalse($filter->isActive());
    }

    public function testFilterClosureNotEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => function ($builder, $alias, $field, $value) {
                return true;
            },
        ]);

        $filter->filter($builder, 'alias', 'field', ['value' => 'myValue']);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterMethodEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->filter($builder, 'alias', 'field', false);
        $filter->filter($builder, 'alias', 'field', 'scalarValue');
        $filter->filter($builder, 'alias', 'field', ['value' => '']);

        $this->assertFalse($filter->isActive());
    }

    public function testFilterMethodNotEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->filter($builder, 'alias', 'field', ['value' => 'myValue']);

        $this->assertTrue($filter->isActive());
    }

    public function customCallback($builder, $alias, $field, $value)
    {
        return true;
    }

    public function testFilterException(): void
    {
        $this->expectException(\RuntimeException::class);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', []);

        $filter->filter($builder, 'alias', 'field', 'myValue');
    }
}
