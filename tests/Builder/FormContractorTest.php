<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Builder;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Builder\FormContractor;
use Symfony\Component\Form\FormFactoryInterface;

class FormContractorTest extends TestCase
{
    /**
     * @var FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formFactory;

    /**
     * @var FormContractor
     */
    private $formContractor;

    protected function setUp()
    {
        $this->formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $this->formContractor = new FormContractor($this->formFactory);
    }

    public function testGetFormBuilder()
    {
        $this->formFactory->expects($this->once())->method('createNamedBuilder')
            ->willReturn($this->createMock('Symfony\Component\Form\FormBuilderInterface'));
        $this->assertInstanceOf(
            'Symfony\Component\Form\FormBuilderInterface',
            $this->formContractor->getFormBuilder('test', ['foo' => 'bar'])
        );
    }

    public function testDefaultOptionsForSonataFormTypes()
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $modelManager = $this->createMock('Sonata\AdminBundle\Model\ModelManagerInterface');
        $modelClass = 'FooEntity';
        $admin->method('getModelManager')->willReturn($modelManager);
        $admin->method('getClass')->willReturn($modelClass);
        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->method('getAdmin')->willReturn($admin);
        $fieldDescription->method('getTargetEntity')->willReturn($modelClass);
        $fieldDescription->method('getAssociationAdmin')->willReturn($admin);

        $modelTypes = [
            'sonata_type_model',
            'sonata_type_model_list',
        ];
        $adminTypes = ['sonata_type_admin'];
        $collectionTypes = ['sonata_type_collection'];
        // NEXT_MAJOR: Use only FQCNs when dropping support for Symfony <2.8
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $classTypes = [
                'Sonata\AdminBundle\Form\Type\ModelType',
                'Sonata\AdminBundle\Form\Type\ModelListType',
            ];
            foreach ($classTypes as $classType) {
                array_push(
                    $modelTypes,
                    // add class type.
                    $classType,
                    // add instance of class type.
                    get_class($this->createMock($classType))
                );
            }
            $adminTypes[] = 'Sonata\AdminBundle\Form\Type\AdminType';
            $collectionTypes[] = 'Sonata\CoreBundle\Form\Type\CollectionType';
        }
        // model types
        foreach ($modelTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $fieldDescription);
            $this->assertSame($fieldDescription, $options['sonata_field_description']);
            $this->assertSame($modelClass, $options['class']);
            $this->assertSame($modelManager, $options['model_manager']);
        }
        // admin type
        $fieldDescription->method('getMappingType')->willReturn(ClassMetadataInfo::ONE);
        foreach ($adminTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $fieldDescription);
            $this->assertSame($fieldDescription, $options['sonata_field_description']);
            $this->assertSame($modelClass, $options['data_class']);
        }
        // collection type
        $fieldDescription->method('getMappingType')->willReturn(ClassMetadataInfo::MANY);
        foreach ($collectionTypes as $index => $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $fieldDescription);
            $this->assertSame($fieldDescription, $options['sonata_field_description']);
            $this->assertSame($adminTypes[$index], $options['type']);
            $this->assertSame(true, $options['modifiable']);
            $this->assertSame($fieldDescription, $options['type_options']['sonata_field_description']);
            $this->assertSame($modelClass, $options['type_options']['data_class']);
        }
    }

    public function testAdminClassAttachForNotMappedField()
    {
        // Given
        $modelManager = $this->createMock('Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager');
        $modelManager->method('hasMetadata')->willReturn(false);
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->method('getModelManager')->willReturn($modelManager);
        $fieldDescription = $this->createMock('Sonata\AdminBundle\Admin\FieldDescriptionInterface');
        $fieldDescription->method('getMappingType')->willReturn('one');
        $fieldDescription->method('getType')->willReturn('sonata_type_model_list');
        $fieldDescription->method('getOption')->with($this->logicalOr(
            $this->equalTo('edit'),
            $this->equalTo('admin_code')
        ))->willReturn('sonata.admin.code');
        // Then
        $admin
            ->expects($this->once())
            ->method('attachAdminClass')
            ->with($fieldDescription)
        ;
        // When
        $this->formContractor->fixFieldDescription($admin, $fieldDescription);
    }
}
