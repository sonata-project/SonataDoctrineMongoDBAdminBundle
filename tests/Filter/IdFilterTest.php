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
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\IdFilter;

final class IdFilterTest extends FilterWithQueryBuilderTest
{
    /**
     * @param mixed $value
     *
     * @dataProvider getNotApplicableValues
     */
    public function testEmpty($value): void
    {
        $filter = new IdFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

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
            [[]],
        ];
    }

    public function testItDoesNotApplyWithWrongObjectId(): void
    {
        $filter = new IdFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        $queryBuilder = $this->getQueryBuilder();

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['value' => 'wrong_object_id', 'type' => null]);
        $this->assertFalse($filter->isActive());
    }

    /**
     * @dataProvider getEqualTypes
     */
    public function testDefaultTypeIsEquals(?int $type): void
    {
        $filter = new IdFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with(new ObjectId('507f1f77bcf86cd799439011'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['value' => '507f1f77bcf86cd799439011', 'type' => $type]);

        $this->assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array{int|null}>
     */
    public function getEqualTypes(): iterable
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
            ->expects($this->once())
            ->method('notEqual')
            ->with(new ObjectId('507f1f77bcf86cd799439011'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['value' => '507f1f77bcf86cd799439011', 'type' => EqualOperatorType::TYPE_NOT_EQUAL]);
        $this->assertTrue($filter->isActive());
    }
}
