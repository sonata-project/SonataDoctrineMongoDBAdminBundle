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

use MongoDB\BSON\ObjectId;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\IdFilter;

final class IdFilterTest extends FilterWithQueryBuilderTest
{
    public function testEmpty(): void
    {
        $filter = new IdFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray([]));

        static::assertFalse($filter->isActive());
    }

    public function testItDoesNotApplyWithWrongObjectId(): void
    {
        $filter = new IdFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        $queryBuilder = $this->getQueryBuilder();

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => 'wrong_object_id', 'type' => null]));
        static::assertFalse($filter->isActive());
    }

    /**
     * @dataProvider provideDefaultTypeIsEqualsCases
     */
    public function testDefaultTypeIsEquals(?int $type): void
    {
        $filter = new IdFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method('equals')
            ->with(new ObjectId('507f1f77bcf86cd799439011'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => '507f1f77bcf86cd799439011', 'type' => $type]));

        static::assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array{int|null}>
     */
    public function provideDefaultTypeIsEqualsCases(): iterable
    {
        yield 'default type' => [null];
        yield 'equals type' => [EqualOperatorType::TYPE_EQUAL];
    }

    public function testNotEquals(): void
    {
        $filter = new IdFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(static::once())
            ->method('notEqual')
            ->with(new ObjectId('507f1f77bcf86cd799439011'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(
            [
            'value' => '507f1f77bcf86cd799439011',
            'type' => EqualOperatorType::TYPE_NOT_EQUAL, ]
        ));
        static::assertTrue($filter->isActive());
    }
}
