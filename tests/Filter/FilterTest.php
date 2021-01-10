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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class FilterTest_Filter extends Filter
{
    public function filter(ProxyQueryInterface $query, string $field, $data): void
    {
        $query->field($field)->equals($data);
    }

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
}

final class FilterTest extends TestCase
{
    public function testFieldDescription(): void
    {
        $filter = new FilterTest_Filter();
        $this->assertSame(['option1' => 2], $filter->getDefaultOptions());
        $this->assertNull($filter->getOption('1'));

        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $this->assertSame(2, $filter->getOption('option1'));
        $this->assertNull($filter->getOption('foo'));
        $this->assertSame('bar', $filter->getOption('foo', 'bar'));

        $this->assertSame('field_name', $filter->getName());
        $this->assertSame(TextType::class, $filter->getFieldType());
        $this->assertSame(['class' => 'FooBar'], $filter->getFieldOptions());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testValues(): void
    {
        $filter = new FilterTest_Filter();
        $this->assertEmpty($filter->getValue());

        $filter->setValue(42);
        $this->assertSame(42, $filter->getValue());
    }

    public function testExceptionOnEmptyFieldName(): void
    {
        $this->expectException(\LogicException::class);

        $filter = new FilterTest_Filter();
        $filter->getFieldName();
    }

    public function testIsActive(): void
    {
        $filter = new FilterTest_Filter();
        $this->assertFalse($filter->isActive());
    }

    public function testUseNameWithParentAssociationMappings(): void
    {
        $filter = new FilterTest_Filter();
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
            ->expects($this->once())
            ->method('field')
            ->with('field.name')
            ->willReturnSelf()
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with('foo')
        ;

        $filter->apply($builder, 'foo');
    }

    public function testUseFieldNameWithoutParentAssociationMappings(): void
    {
        $filter = new FilterTest_Filter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE,
            'field_name' => 'field_name',
            'field_mapping' => true,
        ]);

        $queryBuilder = $this->createMock(Builder::class);

        $builder = new ProxyQuery($queryBuilder);

        $queryBuilder
            ->expects($this->once())
            ->method('field')
            ->with('field_name')
            ->willReturnSelf()
        ;

        $queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with('foo')
        ;

        $filter->apply($builder, 'foo');
    }
}
