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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ShowBuilder;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Symfony\Component\Form\Guess\TypeGuess;

final class ShowBuilderTest extends AbstractModelManagerTestCase
{
    /**
     * @var Stub&TypeGuesserInterface
     */
    private $guesser;

    /**
     * @var ShowBuilder
     */
    private $showBuilder;

    /**
     * @var MockObject&AdminInterface
     */
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guesser = $this->createStub(TypeGuesserInterface::class);

        $this->showBuilder = new ShowBuilder(
            $this->guesser,
            [
                'fakeTemplate' => 'fake',
                FieldDescriptionInterface::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
            ]
        );

        $this->admin = $this->createMock(AdminInterface::class);

        $this->admin->method('getModelManager')->willReturn($this->modelManager);
    }

    public function testGetBaseList(): void
    {
        $this->assertInstanceOf(FieldDescriptionCollection::class, $this->showBuilder->getBaseList());
    }

    public function testAddFieldNoType(): void
    {
        $typeGuess = $this->createStub(TypeGuess::class);

        $fieldDescription = new FieldDescription('FakeName', [], ['type' => ClassMetadata::ONE]);

        $this->admin->expects($this->once())->method('attachAdminClass');
        $this->admin->expects($this->once())->method('addShowFieldDescription');

        $typeGuess->method('getType')->willReturn('fakeType');

        $this->guesser->method('guess')->willReturn($typeGuess);

        $this->metadataFactory->method('hasMetadataFor')->willReturn(false);

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            null,
            $fieldDescription,
            $this->admin
        );

        $this->assertSame('fakeType', $fieldDescription->getType());
    }

    public function testAddFieldWithType(): void
    {
        $fieldDescription = new FieldDescription('FakeName');

        $this->admin->expects($this->once())->method('addShowFieldDescription');

        $this->metadataFactory->method('hasMetadataFor')->willReturn(false);

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            'someType',
            $fieldDescription,
            $this->admin
        );

        $this->assertSame('someType', $fieldDescription->getType());
    }

    /**
     * @dataProvider fixFieldDescriptionData
     */
    public function testFixFieldDescription(string $type, string $property, string $template): void
    {
        $documentClass = DocumentWithReferences::class;
        $classMetadata = $this->getMetadataForDocumentWithAnnotations($documentClass);

        $fieldDescription = new FieldDescription($property, [], $classMetadata->fieldMappings[$property]);

        $this->admin->expects($this->once())->method('attachAdminClass');

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

        $this->showBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($template, $fieldDescription->getTemplate());
        $this->assertSame($classMetadata->fieldMappings[$property], $fieldDescription->getFieldMapping());
    }

    public function fixFieldDescriptionData(): iterable
    {
        return [
            'one' => [
                FieldDescriptionInterface::TYPE_MANY_TO_ONE,
                'associatedDocument',
                '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
            ],
            'many' => [
                FieldDescriptionInterface::TYPE_MANY_TO_MANY,
                'embeddedDocument',
                '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
            ],
        ];
    }

    /**
     * @dataProvider fixFieldDescriptionTypes
     */
    public function testFixFieldDescriptionFixesType(string $expectedType, string $type): void
    {
        $fieldDescription = new FieldDescription('FakeName');
        $fieldDescription->setType($type);

        $this->showBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($expectedType, $fieldDescription->getType());
    }

    public function fixFieldDescriptionTypes(): iterable
    {
        return [
            ['string', 'id'],
            ['integer', 'int'],
        ];
    }

    public function testFixFieldDescriptionException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->showBuilder->fixFieldDescription($this->admin, new FieldDescription('name'));
    }
}
