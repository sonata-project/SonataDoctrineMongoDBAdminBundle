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
use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\Form\Type\BooleanType;

class BooleanFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');
        $filter->filter($builder, 'alias', 'field', 'test');
        $filter->filter($builder, 'alias', 'field', false);

        $filter->filter($builder, 'alias', 'field', []);
        $filter->filter($builder, 'alias', 'field', [null, 'test']);

        $this->assertFalse($filter->isActive());
    }

    public function testFilterNo(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with(false)
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => null, 'value' => BooleanType::TYPE_NO]);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterYes(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with(true)
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => null, 'value' => BooleanType::TYPE_YES]);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterArray(): void
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('in')
            ->with([false])
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => null, 'value' => [BooleanType::TYPE_NO]]);

        $this->assertTrue($filter->isActive());
    }
}
