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
use Sonata\DoctrineMongoDBAdminBundle\Tests\Util\CallbackClass;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class CallbackFilterTest extends FilterWithQueryBuilderTest
{
    use ExpectDeprecationTrait;

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
     * @phpstan-return array<array{mixed}>
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

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @dataProvider provideCallables
     */
    public function testItThrowsDeprecationWithoutFilterData(callable $callable): void
    {
        $proxyQuery = new ProxyQuery($this->getQueryBuilder());

        $filter = new CallbackFilter();
        $filter->initialize('field_name_test', [
            'callback' => $callable,
            'field_name' => 'field_name_test',
        ]);

        $this->expectDeprecation('Not adding "Sonata\AdminBundle\Filter\Model\FilterData" as type declaration for argument 4 is deprecated since sonata-project/doctrine-orm-admin-bundle 3.x and the argument will be a "Sonata\AdminBundle\Filter\Model\FilterData" instance in version 4.0.');

        $filter->apply($proxyQuery, ['value' => 'myValue']);
        $this->assertTrue($filter->isActive());
    }

    /**
     * @phpstan-return iterable<array{callable}>
     */
    public function provideCallables(): iterable
    {
        yield 'static class method call' => [[CallbackClass::class, 'staticCallback']];
        yield 'object method call as array' => [[new CallbackClass(), 'callback']];
        yield 'invokable class with array type declaration' => [new CallbackClass()];
        yield 'anonymous function' => [static function ($query, $alias, $field, $data): bool { return true; }];
    }
}
