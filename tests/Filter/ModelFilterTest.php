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
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\EqualType;

class DocumentStub
{
    private $id;

    public function __construct()
    {
        $this->id = new \MongoId();
    }

    public function getId()
    {
        return (string) ($this->id);
    }
}

class ModelFilterTest extends TestCase
{
    private $queryBuilder;

    protected function setUp()
    {
        $this->queryBuilder = $this->createMock(Builder::class);
    }

    /**
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getFieldDescription(array $options)
    {
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->expects($this->once())->method('getOptions')->willReturn($options);
        $fieldDescription->expects($this->once())->method('getName')->willReturn('field_name');

        return $fieldDescription;
    }

    public function testFilterEmpty()
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $builder = new ProxyQuery($this->queryBuilder);

        $builder->getQueryBuilder()
            ->expects($this->never())
            ->method('field')
        ;

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', []);

        $this->assertFalse($filter->isActive());
    }

    public function testFilterArray()
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar'], 'field_mapping' => true]);

        $builder = new ProxyQuery($this->queryBuilder);

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('field')
            ->with('field.$id')
            ->willReturnSelf()
        ;

        $oneDocument = new DocumentStub();
        $otherDocument = new DocumentStub();

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('in')
            ->with([new \MongoId($oneDocument->getId()), new \MongoId($otherDocument->getId())])
        ;

        $filter->filter($builder, 'alias', 'field', [
            'type' => EqualType::TYPE_IS_EQUAL,
            'value' => [$oneDocument, $otherDocument],
        ]);

        $this->assertTrue($filter->isActive());
    }

    public function testFilterScalar()
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar'], 'field_mapping' => true]);

        $builder = new ProxyQuery($this->queryBuilder);

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('field')
            ->with('field.$id')
            ->willReturnSelf()
        ;

        $document1 = new DocumentStub();

        $builder->getQueryBuilder()
            ->expects($this->once())
            ->method('equals')
            ->with(new \MongoId($document1->getId()))
        ;

        $filter->filter($builder, 'alias', 'field', ['type' => EqualType::TYPE_IS_EQUAL, 'value' => $document1]);

        $this->assertTrue($filter->isActive());
    }

    public function testAssociationWithInvalidMapping()
    {
        $this->expectException(\RuntimeException::class);

        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => 'foo', 'field_mapping' => true]);

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, 'asd');
    }

    public function testAssociationWithValidMappingAndEmptyFieldName()
    {
        $this->expectException(\RuntimeException::class);

        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => ClassMetadata::ONE, 'field_mapping' => true]);

        $builder = new ProxyQuery($this->queryBuilder);

        $filter->apply($builder, 'asd');
        $this->assertTrue($filter->isActive());
    }

    public function testAssociationWithValidMapping()
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', [
            'mapping_type' => ClassMetadata::ONE,
            'field_name' => 'field_name',
            'association_mapping' => [
                'fieldName' => 'association_mapping',
            ], 'field_mapping' => true,
        ]);

        $builder = new ProxyQuery($this->queryBuilder);

        $builder->getQueryBuilder()
            ->method('field')
            ->with('field_name.$id')
            ->willReturnSelf()
        ;

        $filter->apply($builder, ['type' => EqualType::TYPE_IS_EQUAL, 'value' => new DocumentStub()]);

        $this->assertTrue($filter->isActive());
    }

    public function testAssociationWithValidParentAssociationMappings()
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
            ], 'field_mapping' => true,
        ]);

        $builder = new ProxyQuery($this->queryBuilder);

        $builder->getQueryBuilder()
            ->method('field')
            ->with('field_name.$id')
            ->willReturnSelf()
        ;

        $filter->apply($builder, ['type' => EqualType::TYPE_IS_EQUAL, 'value' => new DocumentStub()]);

        $this->assertTrue($filter->isActive());
    }
}
