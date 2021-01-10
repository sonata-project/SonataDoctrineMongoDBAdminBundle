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
     * @param mixed $value
     *
     * @dataProvider getNotApplicableValues
     */
    public function testEmpty($value): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->apply($builder, $value);

        $this->assertFalse($filter->isActive());
    }

    public function getNotApplicableValues(): array
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * @dataProvider getContainsTypes
     */
    public function testContains($containsType): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with(new Regex('asd', 'i'))
        ;

        $filter->apply($builder, ['value' => 'asd', 'type' => $containsType]);
        $this->assertTrue($filter->isActive());
    }

    public function getContainsTypes(): array
    {
        return [
            [ContainsOperatorType::TYPE_CONTAINS],
            [null],
        ];
    }

    public function testNotContains(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('not')
            ->with(new Regex('asd', 'i'))
        ;

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

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with('asd')
        ;

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

        $builder = new ProxyQuery($queryBuilder);

        $builder->getQueryBuilder()
            ->method('field')
            ->with('field_name')
            ->willReturnSelf()
        ;

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with('asd')
        ;

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

        $builder = new ProxyQuery($this->getQueryBuilder());
        $builder->getQueryBuilder()->expects($this->once())->method('addOr');
        $filter->apply($builder, ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]);
        $this->assertTrue($filter->isActive());

        $filter->setCondition(Filter::CONDITION_AND);

        $builder = new ProxyQuery($this->getQueryBuilder());
        $builder->getQueryBuilder()->expects($this->never())->method('addOr');
        $filter->apply($builder, ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]);
        $this->assertTrue($filter->isActive());
    }
}
