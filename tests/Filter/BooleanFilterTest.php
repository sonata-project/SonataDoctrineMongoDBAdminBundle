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

use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\Form\Type\BooleanType;

final class BooleanFilterTest extends FilterWithQueryBuilderTest
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
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, $value);

        $this->assertFalse($filter->isActive());
    }

    public function getNotApplicableValues(): array
    {
        return [
            [null],
            [''],
            ['test'],
            [false],
            [[]],
            [[null, 'test']],
        ];
    }

    /**
     * @dataProvider getScalarValues
     */
    public function testFilterScalar(bool $equalsReturnValue, int $value): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with($equalsReturnValue);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['type' => null, 'value' => $value]);

        $this->assertTrue($filter->isActive());
    }

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
            ->expects($this->once())
            ->method('in')
            ->with([false]);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, ['type' => null, 'value' => [BooleanType::TYPE_NO]]);

        $this->assertTrue($filter->isActive());
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
