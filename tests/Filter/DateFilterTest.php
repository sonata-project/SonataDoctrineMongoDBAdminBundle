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
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateFilter;
use Symfony\Component\Form\Extension\Core\Type\DateType;

final class DateFilterTest extends FilterWithQueryBuilderTest
{
    public function testEmpty(): void
    {
        $filter = $this->createFilter();

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray([]));

        $this->assertFalse($filter->isActive());
    }

    public function testGetType(): void
    {
        $this->assertSame(DateType::class, $this->createFilter()->getFieldType());
    }

    public function testFilterRecordsWholeDay(): void
    {
        $filter = $this->createFilter();

        $date = new \DateTime('2016-08-31 23:59:59.0-03:00');
        $datePlusOneDay = new \DateTime('2016-09-01 23:59:59.0-03:00');

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects($this->once())
            ->method('gte')
            ->with($date);

        $queryBuilder
            ->expects($this->once())
            ->method('lt')
            ->with($datePlusOneDay);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => $date]));

        $this->assertTrue($filter->isActive());
    }

    private function createFilter(): DateFilter
    {
        $filter = new DateFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
        ]);

        return $filter;
    }
}
