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
use PHPUnit\Framework\MockObject\Stub;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ListBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Andrew Mor-Yaroslavtsev <andrejs@gmail.com>
 */
class ListBuilderTest extends AbstractBuilderTestCase
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

    /**
     * @var ModelManager&Stub
     */
    protected $modelManager;

    protected function setUp(): void
    {
        $this->typeGuesser = $this->createStub(TypeGuesserInterface::class);

        $this->modelManager = $this->createStub(ModelManager::class);

        $this->admin = $this->createMock(AbstractAdmin::class);
        $this->admin->method('getClass')->willReturn('Foo');
        $this->admin->method('getModelManager')->willReturn($this->modelManager);

        $this->listBuilder = new ListBuilder($this->typeGuesser, [
            'fakeTemplate' => 'fake',
            TemplateRegistry::TYPE_STRING => '@SonataAdmin/CRUD/list_string.html.twig',
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
            ->method('guessType')
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
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $fieldDescription = new FieldDescription('name');
        $fieldDescription->setOption('sortable', true);
        $fieldDescription->setType('string');
        $fieldDescription->setFieldMapping($classMetadata->fieldMappings['name']);

        $this->modelManager
            ->method('getParentMetadataForProperty')
            ->willReturn([$classMetadata, 'name', $parentAssociationMapping = []]);

        $this->listBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame('@SonataAdmin/CRUD/list_string.html.twig', $fieldDescription->getTemplate());
        $this->assertSame($classMetadata->getFieldMapping('name'), $fieldDescription->getFieldMapping());
    }

    /**
     * @dataProvider fixFieldDescriptionData
     */
    public function testFixFieldDescriptionWithAssociationMapping(string $type, string $template): void
    {
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $fieldDescription = new FieldDescription('associatedDocument');
        $fieldDescription->setOption('sortable', true);
        $fieldDescription->setType($type);
        $fieldDescription->setMappingType($type);
        $fieldDescription->setFieldMapping($classMetadata->fieldMappings['associatedDocument']);
        $fieldDescription->setAssociationMapping($classMetadata->associationMappings['associatedDocument']);

        $this->admin
            ->expects($this->once())
            ->method('attachAdminClass');

        $this->modelManager
            ->method('getParentMetadataForProperty')
            ->willReturn([$classMetadata, 'associatedDocument', $parentAssociationMapping = []]);

        $this->listBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($template, $fieldDescription->getTemplate());
        $this->assertSame($classMetadata->associationMappings['associatedDocument'], $fieldDescription->getAssociationMapping());
    }

    public function fixFieldDescriptionData(): array
    {
        return [
            'one-to-one' => [
                ClassMetadata::ONE,
                '@SonataAdmin/CRUD/Association/list_many_to_one.html.twig',
            ],
            'many-to-one' => [
                ClassMetadata::MANY,
                '@SonataAdmin/CRUD/Association/list_many_to_many.html.twig',
            ],
        ];
    }

    /**
     * @dataProvider fixFieldDescriptionTypes
     */
    public function testFixFieldDescriptionFixesType(string $expectedType, string $type): void
    {
        $this->modelManager->method('hasMetadata')->willReturn(false);
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
