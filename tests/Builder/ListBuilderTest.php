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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ListBuilder;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Sonata\DoctrineMongoDBAdminBundle\Tests\ClassMetadataAnnotationTrait;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Andrew Mor-Yaroslavtsev <andrejs@gmail.com>
 */
final class ListBuilderTest extends AbstractModelManagerTestCase
{
    use ClassMetadataAnnotationTrait;

    /**
     * @var TypeGuesserInterface&Stub
     */
    protected $typeGuesser;

    /**
     * @var ListBuilder
     */
    protected $listBuilder;

    /**
     * @var MockObject&AdminInterface<object>
     */
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeGuesser = $this->createStub(TypeGuesserInterface::class);
        $this->admin = $this->createMock(AdminInterface::class);

        $this->listBuilder = new ListBuilder($this->typeGuesser, [
            'fakeTemplate' => 'fake',
            FieldDescriptionInterface::TYPE_STRING => '@SonataAdmin/CRUD/list_string.html.twig',
        ]);
    }

    public function testAddListActionField(): void
    {
        $fieldDescription = new FieldDescription('foo');
        $fieldDescription->setAdmin($this->admin);

        $list = $this->listBuilder->getBaseList();

        $this->admin
            ->expects(static::once())
            ->method('addListFieldDescription');

        $this->listBuilder
            ->addField($list, 'actions', $fieldDescription);

        static::assertSame(
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

        $fieldDescription = new FieldDescription(ListMapper::NAME_ACTIONS);
        $fieldDescription->setAdmin($this->admin);

        $list = $this->listBuilder->getBaseList();

        $this->admin
            ->expects(static::once())
            ->method('addListFieldDescription');

        $this->listBuilder->addField($list, null, $fieldDescription);

        static::assertSame(
            ListMapper::TYPE_ACTIONS,
            $list->get(ListMapper::NAME_ACTIONS)->getType(),
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
        $fieldDescription->setAdmin($this->admin);
        $fieldDescription->setType('string');

        $this->admin
            ->method('getClass')
            ->willReturn($documentClass);

        $this->listBuilder->fixFieldDescription($fieldDescription);

        static::assertSame('@SonataAdmin/CRUD/list_string.html.twig', $fieldDescription->getTemplate());
        static::assertSame($classMetadata->getFieldMapping('name'), $fieldDescription->getFieldMapping());
    }

    public function testFixFieldDescriptionException(): void
    {
        $fieldDescription = new FieldDescription('name');
        $fieldDescription->setAdmin($this->admin);

        $this->expectException(\RuntimeException::class);

        $this->listBuilder->fixFieldDescription($fieldDescription);
    }
}
