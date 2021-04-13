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
    /**
     * @var TypeGuesser
     */
    private $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guesser = new TypeGuesser();
    }

    /**
     * @dataProvider associationData
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

        $this->assertSame($type, $result->getType());
        $this->assertSame(Guess::HIGH_CONFIDENCE, $result->getConfidence());
    }

    /**
     * @phpstan-return array<array{string, string}>
     */
    public function associationData(): array
    {
        return [
            'many-to-one' => [
                ClassMetadata::ONE,
                FieldDescriptionInterface::TYPE_MANY_TO_ONE,
            ],
            'one-to-many' => [
                ClassMetadata::MANY,
                FieldDescriptionInterface::TYPE_MANY_TO_MANY,
            ],
        ];
    }

    /**
     * @dataProvider noAssociationData
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

        $this->assertSame($resultType, $result->getType());
        $this->assertSame($confidence, $result->getConfidence());
    }

    /**
     * @phpstan-return array<array{string, string, int}>
     */
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
