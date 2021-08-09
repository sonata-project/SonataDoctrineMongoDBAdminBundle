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

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

final class NumberFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterEmpty(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(self::never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);
        $filter->apply($builder, FilterData::fromArray([]));

        self::assertFalse($filter->isActive());
    }

    public function testFilterInvalidOperator(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(self::never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['type' => 9999999]));

        self::assertFalse($filter->isActive());
    }

    /**
     * @dataProvider getNumberExamples
     *
     * @phpstan-param array{type?: int, value: int} $data
     */
    public function testFilter(array $data, string $method): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(self::once())
            ->method($method)
            ->with($data['value']);
        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray($data));

        self::assertTrue($filter->isActive());
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

    public function testDefaultValues(): void
    {
        $filter = $this->createFilter();

        self::assertSame(NumberType::class, $filter->getFieldType());
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
