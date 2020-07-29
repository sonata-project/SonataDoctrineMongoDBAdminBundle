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

use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

final class DateTimeFilterTest extends FilterWithQueryBuilderTest
{
    public function testEmpty(): void
    {
        $filter = new DateTimeFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');
        $filter->filter($builder, 'alias', 'field', []);

        $this->assertFalse($filter->isActive());
    }

    public function testGetType(): void
    {
        $this->assertSame(DateTimeType::class, (new DateTimeFilter())->getFieldType());
    }

    /**
     * @dataProvider getExamples
     */
    public function testFilter(array $data, string $method): void
    {
        $filter = new DateTimeFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method($method)
            ->with($data['value'] ?? null)
        ;

        $filter->filter($builder, 'alias', 'field', $data);

        $this->assertTrue($filter->isActive());
    }

    public function getExamples(): array
    {
        return [
            [['type' => DateOperatorType::TYPE_EQUAL, 'value' => new \DateTime('now')], 'range'],
            [['type' => DateOperatorType::TYPE_GREATER_EQUAL, 'value' => new \DateTime('now')], 'gte'],
            [['type' => DateOperatorType::TYPE_GREATER_THAN, 'value' => new \DateTime('now')], 'gt'],
            [['type' => DateOperatorType::TYPE_LESS_EQUAL, 'value' => new \DateTime('now')], 'lte'],
            [['type' => DateOperatorType::TYPE_LESS_THAN, 'value' => new \DateTime('now')], 'lt'],
            [['type' => DateOperatorType::TYPE_NULL], 'equals'],
            [['type' => DateOperatorType::TYPE_NOT_NULL], 'notEqual'],
            [['value' => new \DateTime('now')], 'range'],
        ];
    }
}
