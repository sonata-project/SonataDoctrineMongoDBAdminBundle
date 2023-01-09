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
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ShowBuilder;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Tests\ClassMetadataAnnotationTrait;
use Symfony\Component\Form\Guess\TypeGuess;

final class ShowBuilderTest extends TestCase
{
    use ClassMetadataAnnotationTrait;

    /**
     * @var Stub&TypeGuesserInterface
     */
    private TypeGuesserInterface $guesser;

    private ShowBuilder $showBuilder;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $this->guesser = $this->createStub(TypeGuesserInterface::class);

        $this->showBuilder = new ShowBuilder(
            $this->guesser,
            [
                'fakeTemplate' => 'fake',
                FieldDescriptionInterface::TYPE_MANY_TO_ONE => '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig',
            ]
        );

        $this->admin = $this->createMock(AdminInterface::class);
    }

    public function testAddFieldNoType(): void
    {
        $typeGuess = $this->createStub(TypeGuess::class);

        $fieldDescription = new FieldDescription('FakeName', [], ['type' => ClassMetadata::ONE]);
        $fieldDescription->setAdmin($this->admin);

        $this->admin->expects(static::once())->method('attachAdminClass');
        $this->admin->expects(static::once())->method('addShowFieldDescription');

        $typeGuess->method('getType')->willReturn('fakeType');

        $this->guesser->method('guess')->willReturn($typeGuess);

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            null,
            $fieldDescription
        );

        static::assertSame('fakeType', $fieldDescription->getType());
    }

    public function testAddFieldWithType(): void
    {
        $fieldDescription = new FieldDescription('FakeName');
        $fieldDescription->setAdmin($this->admin);

        $this->admin->expects(static::once())->method('addShowFieldDescription');

        $this->showBuilder->addField(
            new FieldDescriptionCollection(),
            'someType',
            $fieldDescription
        );

        static::assertSame('someType', $fieldDescription->getType());
    }

    public function testFixFieldDescriptionException(): void
    {
        $fieldDescription = new FieldDescription('name');
        $fieldDescription->setAdmin($this->admin);

        $this->expectException(\RuntimeException::class);

        $this->showBuilder->fixFieldDescription($fieldDescription);
    }
}
