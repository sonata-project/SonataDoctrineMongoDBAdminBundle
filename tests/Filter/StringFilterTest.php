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

use Doctrine\ODM\MongoDB\Query\Builder;
use MongoDB\BSON\Regex;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\Filter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;

final class StringFilterTest extends FilterWithQueryBuilderTest
{
    /**
     * @dataProvider getNotApplicableValues
     */
    public function testEmpty(array $value): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'field_options' => ['class' => 'FooBar'],
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

    public function testDefaultTypeIsContains(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with(new Regex('asd', 'i'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['value' => 'asd', 'type' => null]);
        $this->assertTrue($filter->isActive());
    }

    /**
     * @dataProvider getContainsTypes
     *
     * @param mixed $value
     */
    public function testContains(string $method, int $type, $value): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method($method)
            ->with($value);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['value' => 'asd', 'type' => $type]);
        $this->assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return array<array{string, int, mixed}>
     */
    public function getContainsTypes(): array
    {
        return [
            ['equals', ContainsOperatorType::TYPE_CONTAINS, new Regex('asd', 'i')],
            ['equals', ContainsOperatorType::TYPE_EQUAL, 'asd'],
            ['not', ContainsOperatorType::TYPE_NOT_CONTAINS, new Regex('asd', 'i')],
        ];
    }

    public function testNotContains(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('not')
            ->with(new Regex('asd', 'i'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_NOT_CONTAINS]);
        $this->assertTrue($filter->isActive());
    }

    public function testEquals(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with('asd');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_EQUAL]);
        $this->assertTrue($filter->isActive());
    }

    public function testEqualsWithValidParentAssociationMappings(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'format' => '%s',
            'field_name' => 'field_name',
            'parent_association_mappings' => [
                [
                    'fieldName' => 'association_mapping',
                ],
                [
                    'fieldName' => 'sub_association_mapping',
                ],
                [
                    'fieldName' => 'sub_sub_association_mapping',
                ],
            ],
        ]);

        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder
            ->method('field')
            ->with('field_name')
            ->willReturnSelf();

        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with('asd');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['type' => ContainsOperatorType::TYPE_EQUAL, 'value' => 'asd']);
        $this->assertTrue($filter->isActive());
    }

    public function testOr(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);
        $filter->setCondition(Filter::CONDITION_OR);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->expects($this->once())->method('addOr');
        $builder = new ProxyQuery($queryBuilder);
        $filter->apply($builder, ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]);
        $this->assertTrue($filter->isActive());

        $filter->setCondition(Filter::CONDITION_AND);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->expects($this->never())->method('addOr');
        $builder = new ProxyQuery($queryBuilder);
        $filter->apply($builder, ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]);
        $this->assertTrue($filter->isActive());
    }
}
