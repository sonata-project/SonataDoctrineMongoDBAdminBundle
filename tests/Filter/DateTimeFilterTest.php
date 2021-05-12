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
    /**
     * @dataProvider getNotApplicableValues
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
     * @phpstan-return array<array{mixed}>
     */
    public function getNotApplicableValues(): array
    {
        return [
            [[]],
        ];
    }

    public function testGetType(): void
    {
        $this->assertSame(DateTimeType::class, $this->createFilter()->getFieldType());
    }

    /**
     * @dataProvider getExamples
     */
    public function testFilter(array $data, string $method): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method($method)
            ->with($data['value'] ?? null);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray($data));

        $this->assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return array<array{array{type?: int, value?: \DateTime}, string}>
     */
    public function getExamples(): array
    {
        return [
            [['type' => DateOperatorType::TYPE_EQUAL, 'value' => new \DateTime('now')], 'equals'],
            [['type' => DateOperatorType::TYPE_GREATER_EQUAL, 'value' => new \DateTime('now')], 'gte'],
            [['type' => DateOperatorType::TYPE_GREATER_THAN, 'value' => new \DateTime('now')], 'gt'],
            [['type' => DateOperatorType::TYPE_LESS_EQUAL, 'value' => new \DateTime('now')], 'lte'],
            [['type' => DateOperatorType::TYPE_LESS_THAN, 'value' => new \DateTime('now')], 'lt'],
            [['value' => new \DateTime('now')], 'equals'],
        ];
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
