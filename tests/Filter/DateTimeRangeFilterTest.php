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
     * @dataProvider getNotApplicableValues
     *
     * @phpstan-param array{start?: mixed, end?: mixed} $value
     */
    public function testEmpty(array $value): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray($value));

        $this->assertFalse($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array{start?: mixed, end?: mixed}>
     */
    public function getNotApplicableValues(): iterable
    {
        return [
            [[]],
            [['end' => new \DateTime()]],
            [['start' => new \DateTime()]],
            [['start' => new \stdClass(), 'end' => new \DateTimeImmutable()]],
            [['start' => new \DateTimeImmutable(), 'end' => new \stdClass()]],
        ];
    }

    public function testGetType(): void
    {
        $this->assertSame(DateTimeRangeType::class, $this->createFilter()->getFieldType());
    }

    /**
     * @dataProvider getBetweenTypes
     */
    public function testFilterBetween(?int $type): void
    {
        $filter = $this->createFilter();

        $startDate = new \DateTimeImmutable();
        $endDate = new \DateTimeImmutable('+1 day');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('gte')
            ->with($startDate);

        $queryBuilder
            ->expects($this->once())
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

        $this->assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array{int|null}>
     */
    public function getBetweenTypes(): iterable
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
            ->expects($this->once())
            ->method('lt')
            ->with($startDate);

        $queryBuilder
            ->expects($this->once())
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

        $this->assertTrue($filter->isActive());
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
