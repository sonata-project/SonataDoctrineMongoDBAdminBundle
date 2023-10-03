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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\FieldDescription;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\TypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Symfony\Component\Form\Guess\Guess;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class TypeGuesserTest extends AbstractModelManagerTestCase
{
    private TypeGuesser $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guesser = new TypeGuesser();
    }

    /**
     * @dataProvider provideGuessTypeWithAssociationCases
     */
    public function testGuessTypeWithAssociation(string $mappingType, string $type): void
    {
        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getMappingType')
            ->willReturn($mappingType);

        $fieldDescription
            ->method('getAssociationMapping')
            ->willReturn(['something']);

        $fieldDescription
            ->method('getFieldMapping')
            ->willReturn(['something']);

        $result = $this->guesser->guess($fieldDescription);

        static::assertSame($type, $result->getType());
        static::assertSame(Guess::HIGH_CONFIDENCE, $result->getConfidence());
    }

    /**
     * @phpstan-return iterable<array{string, string}>
     */
    public function provideGuessTypeWithAssociationCases(): iterable
    {
        yield 'many-to-one' => [
            ClassMetadata::ONE,
            FieldDescriptionInterface::TYPE_MANY_TO_ONE,
        ];
        yield 'one-to-many' => [
            ClassMetadata::MANY,
            FieldDescriptionInterface::TYPE_MANY_TO_MANY,
        ];
    }

    /**
     * @dataProvider provideGuessTypeNoAssociationCases
     */
    public function testGuessTypeNoAssociation(string $type, string $resultType, int $confidence): void
    {
        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getMappingType')
            ->willReturn($type);

        $fieldDescription
            ->method('getAssociationMapping')
            ->willReturn([]);

        $fieldDescription
            ->method('getFieldMapping')
            ->willReturn(['something']);

        $result = $this->guesser->guess($fieldDescription);

        static::assertSame($resultType, $result->getType());
        static::assertSame($confidence, $result->getConfidence());
    }

    /**
     * @phpstan-return iterable<array{string, string, int}>
     */
    public function provideGuessTypeNoAssociationCases(): iterable
    {
        yield 'collection' => [
            Type::COLLECTION,
            FieldDescriptionInterface::TYPE_ARRAY,
            Guess::HIGH_CONFIDENCE,
        ];
        yield 'hash' => [
            Type::HASH,
            FieldDescriptionInterface::TYPE_ARRAY,
            Guess::HIGH_CONFIDENCE,
        ];
        yield 'bool' => [
            Type::BOOL,
            FieldDescriptionInterface::TYPE_BOOLEAN,
            Guess::HIGH_CONFIDENCE,
        ];
        yield 'timestamp' => [
            Type::TIMESTAMP,
            FieldDescriptionInterface::TYPE_DATETIME,
            Guess::HIGH_CONFIDENCE,
        ];
        yield 'date' => [
            Type::DATE,
            FieldDescriptionInterface::TYPE_DATE,
            Guess::HIGH_CONFIDENCE,
        ];
        yield 'date_immutable' => [
            Type::DATE_IMMUTABLE,
            FieldDescriptionInterface::TYPE_DATE,
            Guess::HIGH_CONFIDENCE,
        ];
        yield 'float' => [
            Type::FLOAT,
            FieldDescriptionInterface::TYPE_FLOAT,
            Guess::MEDIUM_CONFIDENCE,
        ];
        yield 'integer' => [
            Type::INT,
            FieldDescriptionInterface::TYPE_INTEGER,
            Guess::MEDIUM_CONFIDENCE,
        ];
        yield 'string' => [
            Type::STRING,
            FieldDescriptionInterface::TYPE_STRING,
            Guess::MEDIUM_CONFIDENCE,
        ];
        yield 'somefake' => [
            'somefake',
            FieldDescriptionInterface::TYPE_STRING,
            Guess::LOW_CONFIDENCE,
        ];
    }
}
