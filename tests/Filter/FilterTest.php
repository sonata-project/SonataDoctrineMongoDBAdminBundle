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

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class TestFilter extends Filter
{
    public function getDefaultOptions(): array
    {
        return ['option1' => 2];
    }

    public function getRenderSettings(): array
    {
        return ['sonata_type_filter_default', [
            'type' => $this->getFieldType(),
            'options' => $this->getFieldOptions(),
        ]];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        $query->getQueryBuilder()->field($field)->equals($data->getValue());
    }
}

final class FilterTest extends TestCase
{
    public function testFieldDescription(): void
    {
        $filter = new TestFilter();
        self::assertSame(['option1' => 2], $filter->getDefaultOptions());
        self::assertNull($filter->getOption('1'));

        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        self::assertSame(2, $filter->getOption('option1'));
        self::assertNull($filter->getOption('foo'));
        self::assertSame('bar', $filter->getOption('foo', 'bar'));

        self::assertSame('field_name', $filter->getName());
        self::assertSame(TextType::class, $filter->getFieldType());
        self::assertSame(['class' => 'FooBar'], $filter->getFieldOptions());
    }

    public function testExceptionOnEmptyFieldName(): void
    {
        $this->expectException(\LogicException::class);

        $filter = new TestFilter();
        $filter->getFieldName();
    }

    public function testIsActive(): void
    {
        $filter = new TestFilter();
        self::assertFalse($filter->isActive());
    }

    public function testUseNameWithParentAssociationMappings(): void
    {
        $filter = new TestFilter();
        $filter->initialize('field.name', [
            'mapping_type' => ClassMetadata::ONE,
            'field_name' => 'field_name',
            'parent_association_mappings' => [
                [
                    'fieldName' => 'field',
                ],
            ], 'field_mapping' => true,
        ]);

        $queryBuilder = $this->createMock(Builder::class);

        $builder = new ProxyQuery($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('field')
            ->with('field.name')
            ->willReturnSelf();

        $queryBuilder
            ->expects(self::once())
            ->method('equals')
            ->with('foo');

        $filter->apply($builder, FilterData::fromArray(['value' => 'foo']));
    }

    public function testUseFieldNameWithoutParentAssociationMappings(): void
    {
        $filter = new TestFilter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE,
            'field_name' => 'field_name',
            'field_mapping' => true,
        ]);

        $queryBuilder = $this->createMock(Builder::class);

        $builder = new ProxyQuery($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('field')
            ->with('field_name')
            ->willReturnSelf();

        $queryBuilder
            ->expects(self::once())
            ->method('equals')
            ->with('foo');

        $filter->apply($builder, FilterData::fromArray(['value' => 'foo']));
    }
}
