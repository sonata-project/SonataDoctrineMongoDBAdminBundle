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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Guesser;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\Types\Type;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\DoctrineMongoDBAdminBundle\Guesser\TypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Symfony\Component\Form\Guess\Guess;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class TypeGuesserTest extends AbstractModelManagerTestCase
{
    /**
     * @var TypeGuesser
     */
    private $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guesser = new TypeGuesser();
    }

    public function testGuessTypeNoMetadata(): void
    {
        $class = 'FakeClass';
        $property = 'fakeProperty';

        $this->documentManager
            ->method('getClassMetaData')
            ->willThrowException(new MappingException());

        $result = $this->guesser->guessType($class, $property, $this->modelManager);

        $this->assertSame(FieldDescriptionInterface::TYPE_STRING, $result->getType());
        $this->assertSame(Guess::LOW_CONFIDENCE, $result->getConfidence());
    }

    /**
     * @dataProvider associationData
     */
    public function testGuessTypeWithAssociation(string $mappingType, string $type): void
    {
        $classMetadata = $this->createMock(ClassMetadata::class);

        $class = 'FakeClass';
        $property = 'fakeProperty';

        $classMetadata
            ->method('hasAssociation')
            ->with($property)
            ->willReturn(true);

        $classMetadata->fieldMappings = [$property => ['type' => $mappingType]];

        $this->documentManager
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $result = $this->guesser->guessType($class, $property, $this->modelManager);

        $this->assertSame($type, $result->getType());
        $this->assertSame(Guess::HIGH_CONFIDENCE, $result->getConfidence());
    }

    public function associationData(): array
    {
        return [
            'many-to-one' => [
                ClassMetadata::ONE,
                'mongo_one',
            ],
            'one-to-many' => [
                ClassMetadata::MANY,
                'mongo_many',
            ],
        ];
    }

    /**
     * @dataProvider noAssociationData
     */
    public function testGuessTypeNoAssociation(string $type, string $resultType, int $confidence): void
    {
        $classMetadata = $this->createMock(ClassMetadata::class);

        $class = 'FakeClass';
        $property = 'fakeProperty';

        $classMetadata
            ->method('hasAssociation')
            ->with($property)
            ->willReturn(false);

        $classMetadata
            ->method('getTypeOfField')
            ->with($property)
            ->willReturn($type);

        $this->documentManager
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $result = $this->guesser->guessType($class, $property, $this->modelManager);

        $this->assertSame($resultType, $result->getType());
        $this->assertSame($confidence, $result->getConfidence());
    }

    public function noAssociationData(): array
    {
        return [
            'collection' => [
                Type::COLLECTION,
                FieldDescriptionInterface::TYPE_ARRAY,
                Guess::HIGH_CONFIDENCE,
            ],
            'hash' => [
                Type::HASH,
                FieldDescriptionInterface::TYPE_ARRAY,
                Guess::HIGH_CONFIDENCE,
            ],
            'bool' => [
                Type::BOOL,
                FieldDescriptionInterface::TYPE_BOOLEAN,
                Guess::HIGH_CONFIDENCE,
            ],
            'timestamp' => [
                Type::TIMESTAMP,
                FieldDescriptionInterface::TYPE_DATETIME,
                Guess::HIGH_CONFIDENCE,
            ],
            'date' => [
                Type::DATE,
                FieldDescriptionInterface::TYPE_DATE,
                Guess::HIGH_CONFIDENCE,
            ],
            'date_immutable' => [
                Type::DATE_IMMUTABLE,
                FieldDescriptionInterface::TYPE_DATE,
                Guess::HIGH_CONFIDENCE,
            ],
            'float' => [
                Type::FLOAT,
                FieldDescriptionInterface::TYPE_FLOAT,
                Guess::MEDIUM_CONFIDENCE,
            ],
            'integer' => [
                Type::INT,
                FieldDescriptionInterface::TYPE_INTEGER,
                Guess::MEDIUM_CONFIDENCE,
            ],
            'string' => [
                Type::STRING,
                FieldDescriptionInterface::TYPE_STRING,
                Guess::MEDIUM_CONFIDENCE,
            ],
            'somefake' => [
                'somefake',
                FieldDescriptionInterface::TYPE_STRING,
                Guess::LOW_CONFIDENCE,
            ],
        ];
    }
}
