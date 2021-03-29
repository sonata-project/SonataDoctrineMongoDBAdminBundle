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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ListBuilder;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Andrew Mor-Yaroslavtsev <andrejs@gmail.com>
 */
class ListBuilderTest extends AbstractModelManagerTestCase
{
    /**
     * @var TypeGuesserInterface&Stub
     */
    protected $typeGuesser;

    /**
     * @var ListBuilder
     */
    protected $listBuilder;

    /**
     * @var AdminInterface&MockObject
     */
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeGuesser = $this->createStub(TypeGuesserInterface::class);

        $this->admin = $this->createMock(AbstractAdmin::class);
        $this->admin->method('getModelManager')->willReturn($this->modelManager);

        $this->listBuilder = new ListBuilder($this->typeGuesser, [
            'fakeTemplate' => 'fake',
            FieldDescriptionInterface::TYPE_STRING => '@SonataAdmin/CRUD/list_string.html.twig',
        ]);
    }

    public function testAddListActionField(): void
    {
        $fieldDescription = new FieldDescription('foo');

        $list = $this->listBuilder->getBaseList();

        $this->admin
            ->expects($this->once())
            ->method('addListFieldDescription');

        $this->listBuilder
            ->addField($list, 'actions', $fieldDescription, $this->admin);

        $this->assertSame(
            '@SonataAdmin/CRUD/list__action.html.twig',
            $list->get('foo')->getTemplate(),
            'Custom list action field has a default list action template assigned'
        );
    }

    public function testCorrectFixedActionsFieldType(): void
    {
        $this->typeGuesser
            ->method('guess')
            ->willReturn(
                new TypeGuess('actions', [], Guess::LOW_CONFIDENCE)
            );

        $fieldDescription = new FieldDescription('_action');

        $list = $this->listBuilder->getBaseList();

        $this->admin
            ->expects($this->once())
            ->method('addListFieldDescription');

        $this->listBuilder->addField($list, null, $fieldDescription, $this->admin);

        $this->assertSame(
            'actions',
            $list->get('_action')->getType(),
            'Standard list _action field has "actions" type'
        );
    }

    public function testFixFieldDescriptionWithFieldMapping(): void
    {
        $documentClass = DocumentWithReferences::class;
        $classMetadata = $this->getMetadataForDocumentWithAnnotations($documentClass);

        $fieldDescription = new FieldDescription(
            'name',
            ['sortable' => true],
            $classMetadata->fieldMappings['name']
        );
        $fieldDescription->setType('string');

        $this->metadataFactory
            ->method('hasMetadataFor')
            ->with($documentClass)
            ->willReturn(true);

        $this->documentManager
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $this->admin
            ->method('getClass')
            ->willReturn($documentClass);

        $this->listBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame('@SonataAdmin/CRUD/list_string.html.twig', $fieldDescription->getTemplate());
        $this->assertSame($classMetadata->getFieldMapping('name'), $fieldDescription->getFieldMapping());
    }

    /**
     * @dataProvider fixFieldDescriptionData
     */
    public function testFixFieldDescriptionWithAssociationMapping(string $property, string $template): void
    {
        $documentClass = DocumentWithReferences::class;
        $classMetadata = $this->getMetadataForDocumentWithAnnotations($documentClass);

        $fieldDescription = new FieldDescription(
            $property,
            ['sortable' => true],
            $classMetadata->fieldMappings[$property],
            $classMetadata->associationMappings[$property]
        );

        $this->admin
            ->expects($this->once())
            ->method('attachAdminClass');

        $this->metadataFactory
            ->method('hasMetadataFor')
            ->with($documentClass)
            ->willReturn(true);

        $this->documentManager
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $this->admin
            ->method('getClass')
            ->willReturn($documentClass);

        $this->listBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($template, $fieldDescription->getTemplate());
        $this->assertSame($classMetadata->associationMappings[$property], $fieldDescription->getAssociationMapping());
    }

    public function fixFieldDescriptionData(): array
    {
        return [
            'one-to-one' => [
                'embeddedDocument',
                '@SonataAdmin/CRUD/Association/list_many_to_one.html.twig',
            ],
            'many-to-one' => [
                'embeddedDocuments',
                '@SonataAdmin/CRUD/Association/list_many_to_many.html.twig',
            ],
        ];
    }

    /**
     * @dataProvider fixFieldDescriptionTypes
     */
    public function testFixFieldDescriptionFixesType(string $expectedType, string $type): void
    {
        $this->metadataFactory->method('hasMetadataFor')->willReturn(false);
        $fieldDescription = new FieldDescription('test');
        $fieldDescription->setType($type);

        $this->listBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($expectedType, $fieldDescription->getType());
    }

    public function fixFieldDescriptionTypes(): array
    {
        return [
            ['string', 'id'],
            ['integer', 'int'],
        ];
    }

    public function testFixFieldDescriptionException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->listBuilder->fixFieldDescription($this->admin, new FieldDescription('name'));
    }
}
