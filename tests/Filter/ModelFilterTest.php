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
use MongoDB\BSON\ObjectId;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\EqualType;

class DocumentStub
{
    public function getId()
    {
        return decbin(random_int(0, getrandmax()));
    }
}

class ModelFilterTest extends FilterWithQueryBuilderTest
{
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

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', []);

        $this->assertFalse($filter->isActive());
    }

    public function testFilterArray()
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar'], 'field_mapping' => true]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', [
            'type' => EqualType::TYPE_IS_EQUAL,
            'value' => [new DocumentStub(), new DocumentStub()],
        ]);

        // the alias is now computer by the entityJoin method
        $this->assertTrue($filter->isActive());
    }

    public function testFilterScalar()
    {
        $filter = new ModelFilter();
        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar'], 'field_mapping' => true]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', ['type' => EqualType::TYPE_IS_EQUAL, 'value' => new DocumentStub()]);

        $this->assertTrue($filter->isActive());
    }

    public function testAssociationWithInvalidMapping()
    {
        $this->expectException(\RuntimeException::class);

        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => 'foo', 'field_mapping' => true]);

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->apply($builder, 'asd');
    }

    public function testAssociationWithValidMappingAndEmptyFieldName()
    {
        $this->expectException(\RuntimeException::class);

        $filter = new ModelFilter();
        $filter->initialize('field_name', ['mapping_type' => ClassMetadata::ONE, 'field_mapping' => true]);

        $builder = new ProxyQuery($this->getQueryBuilder());

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

        $builder = new ProxyQuery($this->getQueryBuilder());

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

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->apply($builder, ['type' => EqualType::TYPE_IS_EQUAL, 'value' => new DocumentStub()]);

        $this->assertTrue($filter->isActive());
    }
}
