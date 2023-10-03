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
use Sonata\AdminBundle\Form\Type\Operator\DateRangeOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\Form\Type\DateTimeRangeType;

final class DateTimeRangeFilterTest extends FilterWithQueryBuilderTest
{
    /**
     * @dataProvider provideEmptyCases
     *
     * @phpstan-param array{start?: mixed, end?: mixed} $value
     */
    public function testEmpty(array $value): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => $value]));

        static::assertFalse($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array<array{start?: mixed, end?: mixed}>>
     */
    public function provideEmptyCases(): iterable
    {
        yield [[]];
        yield [['end' => new \DateTime()]];
        yield [['start' => new \DateTime()]];
    }

    public function testGetType(): void
    {
        static::assertSame(DateTimeRangeType::class, $this->createFilter()->getFieldType());
    }

    /**
     * @dataProvider provideFilterBetweenCases
     */
    public function testFilterBetween(?int $type): void
    {
        $filter = $this->createFilter();

        $startDate = new \DateTimeImmutable();
        $endDate = new \DateTimeImmutable('+1 day');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method('gte')
            ->with($startDate);

        $queryBuilder
            ->expects(static::once())
            ->method('lte')
            ->with($endDate);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray([
            'type' => $type,
            'value' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]));

        static::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array{int|null}>
     */
    public function provideFilterBetweenCases(): iterable
    {
        yield 'default' => [null];
        yield 'between' => [DateRangeOperatorType::TYPE_BETWEEN];
    }

    public function testFilterNotBetween(): void
    {
        $filter = $this->createFilter();

        $startDate = new \DateTimeImmutable();
        $endDate = new \DateTimeImmutable('+1 day');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method('lt')
            ->with($startDate);

        $queryBuilder
            ->expects(static::once())
            ->method('gt')
            ->with($endDate);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray([
            'type' => DateRangeOperatorType::TYPE_NOT_BETWEEN,
            'value' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]));

        static::assertTrue($filter->isActive());
    }

    private function createFilter(): DateTimeRangeFilter
    {
        $filter = new DateTimeRangeFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        return $filter;
    }
}
