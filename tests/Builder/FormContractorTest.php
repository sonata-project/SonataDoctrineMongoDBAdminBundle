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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Builder;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Builder\FormContractor;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

final class FormContractorTest extends AbstractModelManagerTestCase
{
    /**
     * @var FormFactoryInterface&MockObject
     */
    private $formFactory;

    /**
     * @var FormContractor
     */
    private $formContractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formFactory = $this->createMock(FormFactoryInterface::class);

        $this->formContractor = new FormContractor($this->formFactory);
    }

    public function testGetFormBuilder(): void
    {
        $this->formFactory->expects($this->once())->method('createNamedBuilder')
            ->willReturn($this->createMock(FormBuilderInterface::class));

        $this->assertInstanceOf(
            FormBuilderInterface::class,
            $this->formContractor->getFormBuilder('test', ['foo' => 'bar'])
        );
    }

    public function testDefaultOptionsForSonataFormTypes(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $modelClass = 'FooEntity';

        $admin->method('getModelManager')->willReturn($this->modelManager);
        $admin->method('getClass')->willReturn($modelClass);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->method('getAdmin')->willReturn($admin);
        $fieldDescription->method('getTargetModel')->willReturn($modelClass);
        $fieldDescription->method('getAssociationAdmin')->willReturn($admin);

        $modelTypes = [
            ModelType::class,
            ModelListType::class,
            ModelHiddenType::class,
            ModelAutocompleteType::class,
        ];
        $adminTypes = [
            AdminType::class,
        ];
        $collectionTypes = [
            CollectionType::class,
        ];

        // model types
        foreach ($modelTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $fieldDescription);
            $this->assertSame($fieldDescription, $options['sonata_field_description']);
            $this->assertSame($modelClass, $options['class']);
            $this->assertSame($this->modelManager, $options['model_manager']);
        }

        // admin type
        $fieldDescription->method('getMappingType')->willReturn(ClassMetadata::ONE);
        foreach ($adminTypes as $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $fieldDescription);
            $this->assertSame($fieldDescription, $options['sonata_field_description']);
            $this->assertSame($modelClass, $options['data_class']);
            $this->assertFalse($options['btn_add']);
            $this->assertFalse($options['delete']);
        }

        // collection type
        $fieldDescription->method('getMappingType')->willReturn(ClassMetadata::MANY);
        foreach ($collectionTypes as $index => $formType) {
            $options = $this->formContractor->getDefaultOptions($formType, $fieldDescription, [
                'by_reference' => false,
            ]);
            $this->assertSame($fieldDescription, $options['sonata_field_description']);
            $this->assertSame(AdminType::class, $options['type']);
            $this->assertTrue($options['modifiable']);
            $this->assertSame($fieldDescription, $options['type_options']['sonata_field_description']);
            $this->assertSame($modelClass, $options['type_options']['data_class']);
            $this->assertFalse($options['type_options']['collection_by_reference']);
        }
    }

    public function testAdminClassAttachForNotMappedField(): void
    {
        // Given
        $admin = $this->createMock(AdminInterface::class);

        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);
        $fieldDescription->method('getMappingType')->willReturn(ClassMetadata::ONE);
        $fieldDescription->method('getType')->willReturn(ModelListType::class);
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

    public function testFixFieldDescriptionForFieldMapping(): void
    {
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $admin = $this->createMock(AdminInterface::class);

        $fieldDescription = new FieldDescription('name', [], $classMetadata->fieldMappings['name']);

        $this->formContractor->fixFieldDescription($admin, $fieldDescription);

        $this->assertSame($classMetadata->fieldMappings['name'], $fieldDescription->getFieldMapping());
    }

    public function testFixFieldDescriptionForAssociationMapping(): void
    {
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $admin = $this->createMock(AdminInterface::class);

        $fieldDescription = new FieldDescription(
            'associatedDocument',
            [],
            $classMetadata->fieldMappings['associatedDocument'],
            $classMetadata->associationMappings['associatedDocument']
        );

        $this->formContractor->fixFieldDescription($admin, $fieldDescription);

        $this->assertSame($classMetadata->associationMappings['associatedDocument'], $fieldDescription->getAssociationMapping());
    }
}
