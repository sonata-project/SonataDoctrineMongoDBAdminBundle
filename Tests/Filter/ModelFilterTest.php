<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Filter;

use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Sonata\CoreBundle\Form\Type\EqualType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;

class DocumentStub
{
    public function getId()
    {
        return decbin(rand());
    }

}

class ModelFilterTest extends FilterWithQueryBuilderTest
{
    /**
     * @param  array                                               $options
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getFieldDescription(array $options)
    {
        $fieldDescription = $this->getMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->expects($this->once())->method('getOptions')->will($this->returnValue($options));
        $fieldDescription->expects($this->once())->method('getName')->will($this->returnValue('field_name'));

        return $fieldDescription;
    }

    public function testFilterEmpty()
    {
        $filter = new ModelFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', array());

        $this->assertEquals(false, $filter->isActive());
    }

    public function testFilterArray()
    {
        $filter = new ModelFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar'), 'field_mapping' => true));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array(
            'type' => EqualType::TYPE_IS_EQUAL,
            'value' => array(new DocumentStub(), new DocumentStub())
        ));

        // the alias is now computer by the entityJoin method
        $this->assertEquals(true, $filter->isActive());
    }

    public function testFilterScalar()
    {
        $filter = new ModelFilter;
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar'), 'field_mapping' => true));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('type' => EqualType::TYPE_IS_EQUAL, 'value' => new DocumentStub()));

        $this->assertEquals(true, $filter->isActive());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAssociationWithInvalidMapping()
    {
        $filter = new ModelFilter;
        $filter->initialize('field_name', array('mapping_type' => 'foo', 'field_mapping' => true));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->apply($builder, 'asd');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testAssociationWithValidMappingAndEmptyFieldName()
    {
        $filter = new ModelFilter;
        $filter->initialize('field_name', array('mapping_type' => ClassMetadataInfo::ONE, 'field_mapping' => true));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->apply($builder, 'asd');
        $this->assertEquals(true, $filter->isActive());
    }

    public function testAssociationWithValidMapping()
    {
        $filter = new ModelFilter;
        $filter->initialize('field_name', array(
            'mapping_type' => ClassMetadataInfo::ONE,
            'field_name' => 'field_name',
            'association_mapping' => array(
                'fieldName' => 'association_mapping'
            ), 'field_mapping' => true
        ));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->apply($builder, array('type' => EqualType::TYPE_IS_EQUAL, 'value' => new DocumentStub()));

        $this->assertEquals(true, $filter->isActive());
    }

    public function testAssociationWithValidParentAssociationMappings()
    {
        $filter = new ModelFilter;
        $filter->initialize('field_name', array(
            'mapping_type' => ClassMetadataInfo::ONE,
            'field_name' => 'field_name',
            'parent_association_mappings' => array(
                array(
                    'fieldName' => 'association_mapping'
                ),
                array(
                    'fieldName' => 'sub_association_mapping'
                ),
            ),
            'association_mapping' => array(
                'fieldName' => 'sub_sub_association_mapping'
            ), 'field_mapping' => true
        ));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->apply($builder, array('type' => EqualType::TYPE_IS_EQUAL, 'value' => new DocumentStub()));

        $this->assertEquals(true, $filter->isActive());
    }

}
