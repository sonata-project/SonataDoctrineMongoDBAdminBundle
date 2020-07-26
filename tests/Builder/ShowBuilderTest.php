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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ShowBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ShowBuilderTest extends TestCase
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

    /**
     * @var Stub&ModelManager
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->guesser = $this->createStub(TypeGuesserInterface::class);

        $this->showBuilder = new ShowBuilder(
            $this->guesser,
            [
                'fakeTemplate' => 'fake',
                TemplateRegistry::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
                TemplateRegistry::TYPE_MANY_TO_MANY => '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
            ]
        );

        $this->admin = $this->createMock(AdminInterface::class);
        $this->modelManager = $this->createStub(ModelManager::class);

        $this->admin->method('getClass')->willReturn('FakeClass');
        $this->admin->method('getModelManager')->willReturn($this->modelManager);
    }

    public function testGetBaseList(): void
    {
        $this->assertInstanceOf(FieldDescriptionCollection::class, $this->showBuilder->getBaseList());
    }

    public function testAddFieldNoType(): void
    {
        $typeGuess = $this->createStub(TypeGuess::class);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('FakeName');
        $fieldDescription->setMappingType(ClassMetadata::ONE);

        $this->admin->expects($this->once())->method('attachAdminClass');
        $this->admin->expects($this->once())->method('addShowFieldDescription');

        $typeGuess->method('getType')->willReturn('fakeType');

        $this->guesser->method('guessType')->willReturn($typeGuess);

        $this->modelManager->method('hasMetadata')->willReturn(false);

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            null,
            $fieldDescription,
            $this->admin
        );
    }

    public function testAddFieldWithType(): void
    {
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('FakeName');

        $this->admin->expects($this->once())->method('addShowFieldDescription');

        $this->modelManager->method('hasMetadata')->willReturn(false);

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            'someType',
            $fieldDescription,
            $this->admin
        );
    }

    /**
     * @dataProvider fixFieldDescriptionData
     */
    public function testFixFieldDescription(string $type, string $mappingType, string $template): void
    {
        $classMetadata = $this->createStub(ClassMetadata::class);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('FakeName');
        $fieldDescription->setType($type);
        $fieldDescription->setMappingType($mappingType);

        $this->admin->expects($this->once())->method('attachAdminClass');

        $this->modelManager->method('hasMetadata')->willReturn(true);

        $this->modelManager->method('getParentMetadataForProperty')
            ->willReturn([$classMetadata, 2, $parentAssociationMapping = []]);

        $classMetadata->fieldMappings = [2 => []];

        $classMetadata->associationMappings = [2 => ['fieldName' => 'fakeField']];

        $this->showBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($template, $fieldDescription->getTemplate());
    }

    public function fixFieldDescriptionData(): iterable
    {
        return [
            'one' => [
                TemplateRegistry::TYPE_MANY_TO_ONE,
                ClassMetadata::ONE,
                '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
            ],
            'many' => [
                TemplateRegistry::TYPE_MANY_TO_MANY,
                ClassMetadata::MANY,
                '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig',
            ],
        ];
    }

    public function testFixFieldDescriptionException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->showBuilder->fixFieldDescription($this->admin, new FieldDescription());
    }
}
