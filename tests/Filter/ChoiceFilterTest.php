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

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ChoiceFilter;

class ChoiceFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', []);

        $this->assertFalse($filter->isActive());
    }

    public function testFilterArray(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('in')
            ->with(['1', '2'])
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => ChoiceType::TYPE_CONTAINS, 'value' => ['1', '2']]);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterScalar(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with('1')
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => ChoiceType::TYPE_CONTAINS, 'value' => '1']);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterZero(): void
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with('0')
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => ChoiceType::TYPE_CONTAINS, 'value' => 0]);

        $this->assertTrue($filter->isActive());
    }
}
