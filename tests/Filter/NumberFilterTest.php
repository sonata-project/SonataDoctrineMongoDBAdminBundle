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

use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;

class NumberFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty(): void
    {
        $filter = new NumberFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', 'asds');

        $this->assertFalse($filter->isActive());
    }

    public function testFilterInvalidOperator(): void
    {
        $filter = new NumberFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => 'foo']);

        $this->assertFalse($filter->isActive());
    }

    /**
     * @dataProvider getNumberExamples
     */
    public function testFilter(array $data, string $method): void
    {
        $filter = new NumberFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method($method)
            ->with($data['value'])
        ;

        $filter->filter($builder, 'alias', 'field', $data);

        $this->assertTrue($filter->isActive());
    }

    public function getNumberExamples(): array
    {
        return [
            [['type' => NumberOperatorType::TYPE_EQUAL, 'value' => 42], 'equals'],
            [['type' => NumberOperatorType::TYPE_GREATER_EQUAL, 'value' => 42], 'gte'],
            [['type' => NumberOperatorType::TYPE_GREATER_THAN, 'value' => 42], 'gt'],
            [['type' => NumberOperatorType::TYPE_LESS_EQUAL, 'value' => 42], 'lte'],
            [['type' => NumberOperatorType::TYPE_LESS_THAN, 'value' => 42], 'lt'],
            [['value' => 42], 'equals'],
        ];
    }
}
