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

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\Types\Type;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;
use Sonata\DoctrineMongoDBAdminBundle\Guesser\FilterTypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Model\MissingPropertyMetadataException;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\EqualType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;

class FilterTypeGuesserTest extends TestCase
{
    private $guesser;
    private $modelManager;

    protected function setUp(): void
    {
        $this->guesser = new FilterTypeGuesser();
        $this->modelManager = $this->prophesize(ModelManager::class);
    }

    public function testThrowsOnMissingField(): void
    {
        $this->expectException(MissingPropertyMetadataException::class);

        $class = 'My\Model';
        $property = 'whatever';

        $metadata = $this->prophesize(ClassMetadata::class);
        $metadata->hasAssociation($property)->willReturn(false);

        $this->modelManager->getParentMetadataForProperty($class, $property)->willReturn([
            $metadata->reveal(),
            $property,
            'parent mappings, no idea what it looks like',
        ]);
        $this->guesser->guessType($class, $property, $this->modelManager->reveal());
    }

    public function testGuessTypeNoMetadata(): void
    {
        $this->modelManager->getParentMetadataForProperty(
            $class = 'FakeClass',
            $property = 'fakeProperty'
        )->willThrow(MappingException::class);

        $result = $this->guesser->guessType($class, $property, $this->modelManager->reveal());

        $this->assertFalse($result);
    }

    public function testGuessTypeWithAssociation(): void
    {
        $classMetadata = $this->prophesize(ClassMetadata::class);

        $classMetadata->hasAssociation($property = 'fakeProperty')->willReturn(true);
        $classMetadataObject = $classMetadata->reveal();
        $classMetadataObject->fieldMappings['fakeProperty'] = [
            'type' => ClassMetadata::ONE,
            'targetDocument' => $targetDocument = 'FakeEntity',
            'fieldName' => $fieldName = 'fakeName',
        ];

        $this->modelManager->getParentMetadataForProperty(
            $class = 'FakeClass',
            $property
        )->willReturn([$classMetadataObject, $property, $parentAssociation = 'parentAssociation']);

        $result = $this->guesser->guessType($class, $property, $this->modelManager->reveal());

        $options = $result->getOptions();

        $this->assertSame(ModelFilter::class, $result->getType());
        $this->assertSame(Guess::HIGH_CONFIDENCE, $result->getConfidence());
        $this->assertSame($parentAssociation, $options['parent_association_mappings']);
        $this->assertSame(ClassMetadata::ONE, $options['mapping_type']);
        $this->assertSame(EqualType::class, $options['operator_type']);
        $this->assertSame([], $options['operator_options']);
        $this->assertSame($fieldName, $options['field_name']);
        $this->assertSame(DocumentType::class, $options['field_type']);
        $this->assertSame($targetDocument, $options['field_options']['class']);
    }

    /**
     * @dataProvider noAssociationData
     */
    public function testGuessTypeNoAssociation(string $type, string $resultType, int $confidence, ?string $fieldType = null): void
    {
        $classMetadata = $this->prophesize(ClassMetadata::class);

        $classMetadata->hasAssociation($property = 'fakeProperty')->willReturn(false);

        $classMetadata->fieldMappings = [$property => ['fieldName' => $type]];
        $classMetadata->getTypeOfField($property)->willReturn($type);

        $this->modelManager->getParentMetadataForProperty(
            $class = 'FakeClass',
            $property
        )->willReturn([$classMetadata, $property, 'notUsed']);

        $result = $this->guesser->guessType($class, $property, $this->modelManager->reveal());

        $options = $result->getOptions();

        $this->assertSame($resultType, $result->getType());
        $this->assertSame($type, $options['field_name']);
        $this->assertSame($confidence, $result->getConfidence());
        $this->assertSame([], $options['options']);
        $this->assertSame([], $options['field_options']);

        if ($fieldType) {
            $this->assertSame($fieldType, $options['field_type']);
        }
    }

    public function noAssociationData(): array
    {
        return [
            Type::BOOLEAN => [
                'boolean',
                BooleanFilter::class,
                Guess::HIGH_CONFIDENCE,
                BooleanType::class,
            ],
            'datetime' => [
                'datetime',
                DateTimeFilter::class,
                Guess::HIGH_CONFIDENCE,
            ],
            Type::TIMESTAMP => [
                'datetime',
                DateTimeFilter::class,
                Guess::HIGH_CONFIDENCE,
            ],
            Type::DATE => [
                'date',
                DateFilter::class,
                Guess::HIGH_CONFIDENCE,
            ],
            'decimal' => [
                'decimal',
                NumberFilter::class,
                Guess::MEDIUM_CONFIDENCE,
                NumberType::class,
            ],
            Type::FLOAT => [
                'float',
                NumberFilter::class,
                Guess::MEDIUM_CONFIDENCE,
                NumberType::class,
            ],
            Type::INT => [
                'int',
                NumberFilter::class,
                Guess::MEDIUM_CONFIDENCE,
                NumberType::class,
            ],
            Type::STRING => [
                'string',
                StringFilter::class,
                Guess::MEDIUM_CONFIDENCE,
                TextType::class,
            ],
            'text' => [
                'text',
                StringFilter::class,
                Guess::MEDIUM_CONFIDENCE,
                TextType::class,
            ],
            'somefake' => [
                'somefake',
                StringFilter::class,
                Guess::LOW_CONFIDENCE,
            ],
        ];
    }
}
