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
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\Form\Type\BooleanType;

final class BooleanFilterTest extends FilterWithQueryBuilderTest
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

    /**
     * @dataProvider getScalarValues
     */
    public function testFilterScalar(bool $equalsReturnValue, int $value): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(self::once())
            ->method('equals')
            ->with($equalsReturnValue);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['type' => null, 'value' => $value]));

        self::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return array<array{bool, int}>
     */
    public function getScalarValues(): array
    {
        return [
            [false, BooleanType::TYPE_NO],
            [true, BooleanType::TYPE_YES],
        ];
    }

    public function testFilterArray(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(self::once())
            ->method('in')
            ->with([false]);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['type' => null, 'value' => [BooleanType::TYPE_NO]]));

        self::assertTrue($filter->isActive());
    }

    public function testDefaultValues(): void
    {
        $filter = $this->createFilter();

        self::assertSame(BooleanType::class, $filter->getFieldType());
    }

    private function createFilter(): BooleanFilter
    {
        $filter = new BooleanFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'field_options' => ['class' => 'FooBar'],
        ]);

        return $filter;
    }
}
