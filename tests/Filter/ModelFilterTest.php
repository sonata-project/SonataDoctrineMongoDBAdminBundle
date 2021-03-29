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
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;

class DocumentStub
{
    private $id;

    public function __construct()
    {
        $this->id = new ObjectId();
    }

    public function getId()
    {
        return (string) ($this->id);
    }
}

class ModelFilterTest extends TestCase
{
    private $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(Builder::class);
    }

    /**
     * @return FieldDescriptionInterface
     */
    public function getFieldDescription(array $options)
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getOptions')->willReturn($options);
        $fieldDescription->expects($this->once())->method('getName')->willReturn('field_name');

        return $fieldDescription;
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getNotApplicableValues
     */
    public function testFilterEmpty($value): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'field_name' => 'field',
            'field_options' => ['class' => 'FooBar'],
        ]);

        $this->queryBuilder
            ->expects($this->never())
            ->method('field')
        ;

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, $value);

        $this->assertFalse($filter->isActive());
    }

    public function getNotApplicableValues(): array
    {
        return [
            [null],
            [[]],
        ];
    }

    public function testFilterArray(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'field_name' => 'field',
            'field_options' => [
                'class' => 'FooBar',
            ],
            'field_mapping' => [
                'type' => 'collection',
            ],
        ]);

        $this->queryBuilder
            ->expects($this->once())
            ->method('field')
            ->with('field._id')
            ->willReturnSelf()
        ;

        $oneDocument = new DocumentStub();
        $otherDocument = new DocumentStub();

        $this->queryBuilder
            ->expects($this->once())
            ->method('in')
            ->with([new ObjectId($oneDocument->getId()), new ObjectId($otherDocument->getId())])
        ;

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, [
            'type' => EqualOperatorType::TYPE_EQUAL,
            'value' => [$oneDocument, $otherDocument],
        ]);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterScalar(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'field_name' => 'field',
            'field_options' => [
                'class' => 'FooBar',
            ],
            'field_mapping' => [
                'type' => 'string',
            ],
        ]);

        $this->queryBuilder
            ->expects($this->once())
            ->method('field')
            ->with('field._id')
            ->willReturnSelf()
        ;

        $document1 = new DocumentStub();

        $this->queryBuilder
            ->expects($this->once())
            ->method('equals')
            ->with(new ObjectId($document1->getId()))
        ;

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, ['type' => EqualOperatorType::TYPE_EQUAL, 'value' => $document1]);

        $this->assertTrue($filter->isActive());
    }

    public function testAssociationWithInvalidMapping(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => 'foo', 'field_mapping' => []]);

        $builder = new ProxyQuery($this->queryBuilder);

        $this->expectException(\RuntimeException::class);

        $filter->apply($builder, 'asd');
    }

    public function testAssociationWithValidMappingAndEmptyFieldName(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => ClassMetadata::ONE, 'field_mapping' => []]);

        $builder = new ProxyQuery($this->queryBuilder);

        $this->expectException(\RuntimeException::class);

        $filter->apply($builder, 'asd');
    }

    public function testAssociationWithValidMapping(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE,
            'field_name' => 'field_name',
            'association_mapping' => [
                'fieldName' => 'association_mapping',
            ],
            'field_mapping' => [
                'fieldName' => 'association_mapping',
            ],
        ]);

        $this->queryBuilder
            ->method('field')
            ->with('field_name._id')
            ->willReturnSelf()
        ;

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, ['type' => EqualOperatorType::TYPE_EQUAL, 'value' => new DocumentStub()]);

        $this->assertTrue($filter->isActive());
    }

    public function testAssociationWithValidParentAssociationMappings(): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE,
            'field_name' => 'field_name',
            'parent_association_mappings' => [
                [
                    'fieldName' => 'association_mapping',
                ],
                [
                    'fieldName' => 'sub_association_mapping',
                ],
            ],
            'association_mapping' => [
                'fieldName' => 'sub_sub_association_mapping',
            ],
            'field_mapping' => [
                'fieldName' => 'sub_sub_association_mapping',
            ],
        ]);

        $this->queryBuilder
            ->method('field')
            ->with('field_name._id')
            ->willReturnSelf()
        ;

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, ['type' => EqualOperatorType::TYPE_EQUAL, 'value' => new DocumentStub()]);

        $this->assertTrue($filter->isActive());
    }

    /**
     * @dataProvider getMappings
     */
    public function testDifferentIdentifiersBasedOnMapping(string $storeAs, string $fieldIdentifier): void
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE,
            'field_name' => 'field_name',
            'field_mapping' => [
                'storeAs' => $storeAs,
            ],
        ]);

        $this->queryBuilder
            ->method('field')
            ->with('field_name'.$fieldIdentifier)
            ->willReturnSelf()
        ;

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, ['type' => EqualOperatorType::TYPE_EQUAL, 'value' => new DocumentStub()]);

        $this->assertTrue($filter->isActive());
    }

    public function getMappings(): array
    {
        return [
            [ClassMetadata::REFERENCE_STORE_AS_REF, '.id'],
            [ClassMetadata::REFERENCE_STORE_AS_ID, ''],
            [ClassMetadata::REFERENCE_STORE_AS_DB_REF_WITH_DB, '.$id'],
            [ClassMetadata::REFERENCE_STORE_AS_DB_REF, '.$id'],
        ];
    }
}
