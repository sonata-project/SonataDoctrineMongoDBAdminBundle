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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ListBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Andrew Mor-Yaroslavtsev <andrejs@gmail.com>
 */
class ListBuilderTest extends TestCase
{
    /**
     * @var TypeGuesserInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $typeGuesser;

    /**
     * @var ListBuilder
     */
    protected $listBuilder;

    /**
     * @var AdminInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $admin;

    /**
     * @var ModelManager|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $modelManager;

    protected function setUp(): void
    {
        $this->typeGuesser = $this->prophesize(TypeGuesserInterface::class);

        $this->modelManager = $this->prophesize(ModelManager::class);
        $this->modelManager->hasMetadata(Argument::any())->willReturn(false);

        $this->admin = $this->prophesize(AbstractAdmin::class);
        $this->admin->getClass()->willReturn('Foo');
        $this->admin->getModelManager()->willReturn($this->modelManager);
        $this->admin->addListFieldDescription(Argument::any(), Argument::any())
            ->willReturn();

        $this->listBuilder = new ListBuilder($this->typeGuesser->reveal(), [
            'fakeTemplate' => 'fake',
            TemplateRegistry::TYPE_STRING => '@SonataAdmin/CRUD/list_string.html.twig',
        ]);
    }

    public function testAddListActionField(): void
    {
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('foo');
        $list = $this->listBuilder->getBaseList();
        $this->listBuilder
            ->addField($list, 'actions', $fieldDescription, $this->admin->reveal());

        $this->assertSame(
            '@SonataAdmin/CRUD/list__action.html.twig',
            $list->get('foo')->getTemplate(),
            'Custom list action field has a default list action template assigned'
        );
    }

    public function testCorrectFixedActionsFieldType(): void
    {
        $this->typeGuesser->guessType(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn(
            new TypeGuess('actions', [], Guess::LOW_CONFIDENCE)
        );

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('_action');
        $list = $this->listBuilder->getBaseList();
        $this->listBuilder->addField($list, null, $fieldDescription, $this->admin->reveal());

        $this->assertSame(
            'actions',
            $list->get('_action')->getType(),
            'Standard list _action field has "actions" type'
        );
    }

    public function testFixFieldDescriptionWithFieldMapping(): void
    {
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $this->modelManager->hasMetadata(Argument::any())->willReturn(true);
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('test');
        $fieldDescription->setOption('sortable', true);
        $fieldDescription->setType('string');

        $classMetadata->fieldMappings = ['test' => ['type' => 'string']];
        $this->modelManager->getParentMetadataForProperty(Argument::cetera())
            ->willReturn([$classMetadata, 'test', $parentAssociationMapping = []]);

        $this->listBuilder->fixFieldDescription($this->admin->reveal(), $fieldDescription);

        $this->assertSame('@SonataAdmin/CRUD/list_string.html.twig', $fieldDescription->getTemplate());
        $this->assertSame(['type' => 'string'], $fieldDescription->getFieldMapping());
    }

    /**
     * @dataProvider fixFieldDescriptionData
     */
    public function testFixFieldDescriptionWithAssociationMapping(string $type, string $template): void
    {
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $this->modelManager->hasMetadata(Argument::any())->willReturn(true);
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('test');
        $fieldDescription->setOption('sortable', true);
        $fieldDescription->setType($type);
        $fieldDescription->setMappingType($type);

        $this->admin->attachAdminClass(Argument::any())->shouldBeCalledTimes(1);

        $associationMapping = [
            'fieldName' => 'associatedDocument',
            'name' => 'associatedDocument',
        ];

        $classMetadata->associationMappings = [
            'test' => $associationMapping,
        ];

        $this->modelManager->getParentMetadataForProperty(Argument::cetera())
            ->willReturn([$classMetadata, 'test', $parentAssociationMapping = []]);

        $this->listBuilder->fixFieldDescription($this->admin->reveal(), $fieldDescription);

        $this->assertSame($template, $fieldDescription->getTemplate());
        $this->assertSame($associationMapping, $fieldDescription->getAssociationMapping());
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
        $this->modelManager->hasMetadata(Argument::any())->willReturn(false);
        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('test');
        $fieldDescription->setType($type);

        $this->listBuilder->fixFieldDescription($this->admin->reveal(), $fieldDescription);

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
        $this->listBuilder->fixFieldDescription($this->admin->reveal(), new FieldDescription());
    }
}
