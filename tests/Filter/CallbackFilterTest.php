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
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Filter\CallbackFilter;

final class CallbackFilterTest extends FilterWithQueryBuilderTest
{
    public function testFilterClosureEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'callback' => static function (ProxyQueryInterface $proxyQuery, string $field, FilterData $data): bool {
                return $data->hasValue();
            },
        ]);

        $filter->apply($builder, FilterData::fromArray([]));

        self::assertFalse($filter->isActive());
    }

    public function testFilterClosureNotEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'callback' => static function (): bool {
                return true;
            },
        ]);

        $filter->apply($builder, FilterData::fromArray(['value' => 'myValue']));

        self::assertTrue($filter->isActive());
    }

    public function testFilterMethodEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->apply($builder, FilterData::fromArray([]));

        self::assertFalse($filter->isActive());
    }

    public function testFilterMethodNotEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->apply($builder, FilterData::fromArray(['value' => 'myValue']));

        self::assertTrue($filter->isActive());
    }

    public function customCallback(ProxyQueryInterface $proxyQuery, string $field, FilterData $data): bool
    {
        return $data->hasValue();
    }

    public function testFilterException(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', []);

        $this->expectException(\RuntimeException::class);

        $filter->apply($builder, FilterData::fromArray(['myValue']));
    }
}
