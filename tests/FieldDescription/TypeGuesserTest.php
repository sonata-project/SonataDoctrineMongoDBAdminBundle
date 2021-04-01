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
                'array',
                Guess::HIGH_CONFIDENCE,
            ],
            'hash' => [
                Type::HASH,
                'array',
                Guess::HIGH_CONFIDENCE,
            ],
            'bool' => [
                Type::BOOL,
                'boolean',
                Guess::HIGH_CONFIDENCE,
            ],
            'timestamp' => [
                Type::TIMESTAMP,
                'datetime',
                Guess::HIGH_CONFIDENCE,
            ],
            'date' => [
                Type::DATE,
                'date',
                Guess::HIGH_CONFIDENCE,
            ],
            'date_immutable' => [
                Type::DATE_IMMUTABLE,
                'date',
                Guess::HIGH_CONFIDENCE,
            ],
            'float' => [
                Type::FLOAT,
                'number',
                Guess::MEDIUM_CONFIDENCE,
            ],
            'integer' => [
                Type::INT,
                'integer',
                Guess::MEDIUM_CONFIDENCE,
            ],
            'string' => [
                Type::STRING,
                'text',
                Guess::MEDIUM_CONFIDENCE,
            ],
            'somefake' => [
                'somefake',
                'text',
                Guess::LOW_CONFIDENCE,
            ],
        ];
    }
}
