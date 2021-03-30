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
use Sonata\DoctrineMongoDBAdminBundle\Filter\CallbackFilter;

class CallbackFilterTest extends FilterWithQueryBuilderTest
{
    /**
     * @param mixed $value
     *
     * @dataProvider getNotApplicableValues
     */
    public function testFilterClosureEmpty($value): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'callback' => static function (): bool {
                return true;
            },
        ]);

        $filter->apply($builder, $value);

        $this->assertFalse($filter->isActive());
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

        $filter->apply($builder, ['value' => 'myValue']);

        $this->assertTrue($filter->isActive());
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getNotApplicableValues
     */
    public function testFilterMethodEmpty($value): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->apply($builder, $value);

        $this->assertFalse($filter->isActive());
    }

    /**
     * @return array<array{mixed}>
     */
    public function getNotApplicableValues(): array
    {
        return [
            [false],
            ['scalarValue'],
            [['value' => '']],
        ];
    }

    public function testFilterMethodNotEmpty(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'callback' => [$this, 'customCallback'],
        ]);

        $filter->apply($builder, ['value' => 'myValue']);

        $this->assertTrue($filter->isActive());
    }

    public function customCallback(): bool
    {
        return true;
    }

    public function testFilterException(): void
    {
        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name', []);

        $this->expectException(\RuntimeException::class);

        $filter->apply($builder, 'myValue');
    }
}
