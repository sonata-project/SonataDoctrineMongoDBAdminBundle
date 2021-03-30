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

use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ChoiceFilter;

class ChoiceFilterTest extends FilterWithQueryBuilderTest
{
    /**
     * @param mixed $value
     *
     * @dataProvider getNotApplicableValues
     */
    public function testFilterEmpty($value): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->never())
            ->method('field')
        ;

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, $value);

        $this->assertFalse($filter->isActive());
    }

    /**
     * @return array<array{mixed}>
     */
    public function getNotApplicableValues(): array
    {
        return [
            [null],
            ['all'],
            [[]],
        ];
    }

    public function testFilterArray(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('in')
            ->with(['1', '2'])
        ;

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['type' => ContainsOperatorType::TYPE_CONTAINS, 'value' => ['1', '2']]);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterScalar(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with('1')
        ;

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['type' => ContainsOperatorType::TYPE_CONTAINS, 'value' => '1']);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterZero(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with('0')
        ;

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['type' => ContainsOperatorType::TYPE_CONTAINS, 'value' => 0]);

        $this->assertTrue($filter->isActive());
    }

    private function createFilter(): ChoiceFilter
    {
        $filter = new ChoiceFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'field_options' => ['class' => 'FooBar'],
        ]);

        return $filter;
    }
}
