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
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

final class DateTimeFilterTest extends FilterWithQueryBuilderTest
{
    public function testEmpty(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray([]));

        static::assertFalse($filter->isActive());
    }

    public function testGetType(): void
    {
        static::assertSame(DateTimeType::class, $this->createFilter()->getFieldType());
    }

    /**
     * @dataProvider provideFilterCases
     *
     * @phpstan-param array{type?: int, value?: \DateTime} $data
     */
    public function testFilter(array $data, string $method): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method($method)
            ->with($data['value'] ?? null);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray($data));

        static::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array{array{type?: int, value?: \DateTime}, string}>
     */
    public function provideFilterCases(): iterable
    {
        yield [['type' => DateOperatorType::TYPE_EQUAL, 'value' => new \DateTime('now')], 'equals'];
        yield [['type' => DateOperatorType::TYPE_GREATER_EQUAL, 'value' => new \DateTime('now')], 'gte'];
        yield [['type' => DateOperatorType::TYPE_GREATER_THAN, 'value' => new \DateTime('now')], 'gt'];
        yield [['type' => DateOperatorType::TYPE_LESS_EQUAL, 'value' => new \DateTime('now')], 'lte'];
        yield [['type' => DateOperatorType::TYPE_LESS_THAN, 'value' => new \DateTime('now')], 'lt'];
        yield [['value' => new \DateTime('now')], 'equals'];
    }

    private function createFilter(): DateTimeFilter
    {
        $filter = new DateTimeFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        return $filter;
    }
}
