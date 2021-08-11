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
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class StringFilterTest extends FilterWithQueryBuilderTest
{
    public function testSearchEnabled(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', []);
        self::assertTrue($filter->isSearchEnabled());

        $filter = new StringFilter();
        $filter->initialize('field_name', ['global_search' => false]);
        self::assertFalse($filter->isSearchEnabled());
    }

    public function testEmpty(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'field_options' => ['class' => 'FooBar'],
        ]);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->expects(self::never())
            ->method('field');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray([]));

        self::assertFalse($filter->isActive());
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
            ->expects(self::once())
            ->method('equals')
            ->with(new Regex('asd', 'i'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => 'asd', 'type' => null]));
        self::assertTrue($filter->isActive());
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
            ->expects(self::once())
            ->method($method)
            ->with($value);

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => 'asd', 'type' => $type]));
        self::assertTrue($filter->isActive());
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
            ->expects(self::once())
            ->method('not')
            ->with(new Regex('asd', 'i'));

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => 'asd', 'type' => ContainsOperatorType::TYPE_NOT_CONTAINS]));
        self::assertTrue($filter->isActive());
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
            ->expects(self::once())
            ->method('equals')
            ->with('asd');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['value' => 'asd', 'type' => ContainsOperatorType::TYPE_EQUAL]));
        self::assertTrue($filter->isActive());
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
            ->expects(self::once())
            ->method('equals')
            ->with('asd');

        $builder = new ProxyQuery($queryBuilder);

        $filter->apply($builder, FilterData::fromArray(['type' => ContainsOperatorType::TYPE_EQUAL, 'value' => 'asd']));
        self::assertTrue($filter->isActive());
    }

    public function testOr(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);
        $filter->setCondition(FilterInterface::CONDITION_OR);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->expects(self::once())->method('addOr');
        $builder = new ProxyQuery($queryBuilder);
        $filter->apply($builder, FilterData::fromArray(['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]));
        self::assertTrue($filter->isActive());

        $filter->setCondition(FilterInterface::CONDITION_AND);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->expects(self::never())->method('addOr');
        $builder = new ProxyQuery($queryBuilder);
        $filter->apply($builder, FilterData::fromArray(['value' => 'asd', 'type' => ContainsOperatorType::TYPE_CONTAINS]));
        self::assertTrue($filter->isActive());
    }

    public function testDefaultValues(): void
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', [
            'field_name' => self::DEFAULT_FIELD_NAME,
            'format' => '%s',
        ]);

        self::assertSame(TextType::class, $filter->getFieldType());
    }
}
