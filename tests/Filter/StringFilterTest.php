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

class StringFilterTest extends FilterWithQueryBuilderTest
{
    public function testEmpty(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');

        $this->assertFalse($filter->isActive());
    }

    public function testContains(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['format' => '%s']);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->exactly(2))
            ->method('equals')
            ->with($this->getMongoRegex('asd'))
        ;

        $filter->filter($builder, 'alias', 'field', ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]);
        $filter->filter($builder, 'alias', 'field', ['value' => 'asd', 'type' => null]);
        $this->assertTrue($filter->isActive());
    }

    public function testNotContains(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['format' => '%s']);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('not')
            ->with($this->getMongoRegex('asd'))
        ;

        $filter->filter($builder, 'alias', 'field', ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_NOT_CONTAINS]);
        $this->assertTrue($filter->isActive());
    }

    public function testEquals(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', ['format' => '%s']);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with('asd')
        ;

        $filter->filter($builder, 'alias', 'field', ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_EQUAL]);
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
        $filter->initialize('field_name', ['format' => '%s']);
        $filter->setCondition(Filter::CONDITION_OR);

        $builder = new ProxyQuery($this->getQueryBuilder());
        $builder->getQueryBuilder()->expects($this->once())->method('addOr');
        $filter->filter($builder, 'alias', 'field', ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]);
        $this->assertTrue($filter->isActive());

        $filter->setCondition(Filter::CONDITION_AND);

        $builder = new ProxyQuery($this->getQueryBuilder());
        $builder->getQueryBuilder()->expects($this->never())->method('addOr');
        $filter->filter($builder, 'alias', 'field', ['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]);
        $this->assertTrue($filter->isActive());
    }

    /**
     * NEXT_MAJOR: Use only Regex when dropping support for doctrine/mongodb-odm 1.x.
     *
     * @return Regex|\MongoRegex
     */
    private function getMongoRegex(string $pattern)
    {
        if (class_exists(Regex::class)) {
            return new Regex($pattern, 'i');
        }

        return new \MongoRegex(sprintf('/%s/i', $pattern));
    }
}
