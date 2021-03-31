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
    /**
     * @param mixed $value
     *
     * @dataProvider getNotApplicableValues
     */
    public function testFilterEmpty($value): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);
        $filter->apply($builder, $value);

        $this->assertFalse($filter->isActive());
    }

    /**
     * @phpstan-return array<array{mixed}>
     */
    public function getNotApplicableValues(): array
    {
        return [
            [null],
            ['scalar'],
        ];
    }

    public function testFilterInvalidOperator(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['type' => 'foo']);

        $this->assertFalse($filter->isActive());
    }

    /**
     * @dataProvider getNumberExamples
     */
    public function testFilter(array $data, string $method): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method($method)
            ->with($data['value']);
        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, $data);

        $this->assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return array<array{array{type?: int, value: int}, string}>
     */
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

    private function createFilter(): NumberFilter
    {
        $filter = new NumberFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'field_options' => ['class' => 'FooBar'],
        ]);

        return $filter;
    }
}
