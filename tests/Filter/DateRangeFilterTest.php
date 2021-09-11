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
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangeType;

final class DateRangeFilterTest extends FilterWithQueryBuilderTest
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
            ->expects(static::never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray($value));

        static::assertFalse($filter->isActive());
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
        static::assertSame(DateRangeType::class, $this->createFilter()->getFieldType());
    }

    public function testFilterStartDate(): void
    {
        $filter = $this->createFilter();

        $startDateTime = new \DateTime('2016-08-01');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method('gte')
            ->with($startDateTime);
        $queryBuilder
            ->expects(static::never())
            ->method('lte');

        $proxyQuery = new ProxyQuery($queryBuilder);

        $filter->apply($proxyQuery, FilterData::fromArray([
            'type' => null,
            'value' => [
                'start' => $startDateTime,
                'end' => null,
            ],
        ]));

        static::assertTrue($filter->isActive());
    }

    public function testFilterEndDate(): void
    {
        $filter = $this->createFilter();

        $endDateTime = new \DateTime('2016-08-31');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method('lte')
            ->with($endDateTime);
        $queryBuilder
            ->expects(static::never())
            ->method('gte');

        $proxyQuery = new ProxyQuery($queryBuilder);

        $filter->apply($proxyQuery, FilterData::fromArray([
            'type' => null,
            'value' => [
                'start' => null,
                'end' => $endDateTime,
            ],
        ]));

        static::assertTrue($filter->isActive());
    }

    /**
     * @dataProvider provideDates
     */
    public function testFilterEndDateCoversWholeDay(
        \DateTimeImmutable $expectedEndDateTime,
        \DateTime $viewEndDateTime,
        \DateTimeZone $modelTimeZone
    ): void {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method('lte')
            ->with($expectedEndDateTime);

        $proxyQuery = new ProxyQuery($queryBuilder);

        $startDate = clone $viewEndDateTime;
        $startDate = $startDate->modify('-1 day');

        $modelEndDateTime = clone $viewEndDateTime;
        $modelEndDateTime->setTimezone($modelTimeZone);

        static::assertSame($modelTimeZone->getName(), $modelEndDateTime->getTimezone()->getName());
        static::assertNotSame($modelTimeZone->getName(), $viewEndDateTime->getTimezone()->getName());

        $filter->apply($proxyQuery, FilterData::fromArray([
            'type' => DateRangeOperatorType::TYPE_BETWEEN,
            'value' => [
                'start' => $startDate,
                'end' => $modelEndDateTime,
            ],
        ]));

        static::assertTrue($filter->isActive());
        static::assertSame($expectedEndDateTime->getTimestamp(), $modelEndDateTime->getTimestamp());
    }

    /**
     * @return \Generator<array{\DateTimeImmutable, \DateTime, \DateTimeZone}>
     */
    public function provideDates(): iterable
    {
        yield [
            new \DateTimeImmutable('2016-08-31 23:59:59.0-03:00'),
            new \DateTime('2016-08-31 00:00:00.0-03:00'),
            new \DateTimeZone('UTC'),
        ];

        yield [
            new \DateTimeImmutable('2016-09-01 05:59:59.0-03:00'),
            new \DateTime('2016-08-31 06:00:00.0-03:00'),
            new \DateTimeZone('Antarctica/McMurdo'),
        ];

        yield [
            new \DateTimeImmutable('2016-09-01 06:07:07.0-03:00'),
            new \DateTime('2016-08-31 06:07:08.0-03:00'),
            new \DateTimeZone('Australia/Adelaide'),
        ];

        yield [
            new \DateTimeImmutable('2016-08-31 23:59:59.0-00:00'),
            new \DateTime('2016-08-31 00:00:00.0-00:00'),
            new \DateTimeZone('Pacific/Honolulu'),
        ];

        yield [
            new \DateTimeImmutable('2017-01-01 18:59:59.0+01:00'),
            new \DateTime('2016-12-31 19:00:00.0+01:00'),
            new \DateTimeZone('Africa/Cairo'),
        ];
    }

    private function createFilter(): DateRangeFilter
    {
        $filter = new DateRangeFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        return $filter;
    }
}
