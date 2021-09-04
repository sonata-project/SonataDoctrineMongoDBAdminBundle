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
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;
use Sonata\DoctrineMongoDBAdminBundle\Model\MissingPropertyMetadataException;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\AssociatedDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\ContainerDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\TestDocument;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;

/**
 * NEXT_MAJOR: Remove this file.
 *
 * @group legacy
 */
class FilterTypeGuesserTest extends AbstractModelManagerTestCase
{
    /**
     * @var FilterTypeGuesser
     */
    private $guesser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guesser = new FilterTypeGuesser();
    }

    public function testThrowsOnMissingField(): void
    {
        $className = TestDocument::class;
        $property = 'nonExistingProperty';

        $classMetadata = $this->getMetadataForDocumentWithAnnotations($className);

        $this->documentManager
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $this->expectException(MissingPropertyMetadataException::class);
        $this->guesser->guessType($className, $property, $this->modelManager);
    }

    public function testGuessTypeNoMetadata(): void
    {
        $class = 'FakeClass';
        $property = 'fakeProperty';

        $this->documentManager
            ->method('getClassMetadata')
            ->willThrowException(new MappingException());

        $result = $this->guesser->guessType($class, $property, $this->modelManager);

        static::assertNull($result);
    }

    public function testGuessTypeWithAssociation(): void
    {
        $className = ContainerDocument::class;
        $property = 'associatedDocument';
        $parentAssociation = [];
        $targetDocument = AssociatedDocument::class;

        $classMetadata = $this->getMetadataForDocumentWithAnnotations($className);

        $this->documentManager
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $result = $this->guesser->guessType($className, $property, $this->modelManager);

        $options = $result->getOptions();

        static::assertSame(ModelFilter::class, $result->getType());
        static::assertSame(Guess::HIGH_CONFIDENCE, $result->getConfidence());
        static::assertSame($parentAssociation, $options['parent_association_mappings']);
        static::assertSame(ClassMetadata::ONE, $options['mapping_type']);
        static::assertSame(EqualOperatorType::class, $options['operator_type']);
        static::assertSame([], $options['operator_options']);
        static::assertSame($property, $options['field_name']);
        static::assertSame(DocumentType::class, $options['field_type']);
        static::assertSame($targetDocument, $options['field_options']['class']);
    }

    /**
     * @dataProvider noAssociationData
     */
    public function testGuessTypeNoAssociation(string $type, string $resultType, int $confidence, ?string $fieldType = null): void
    {
        $class = 'FakeClass';
        $property = 'fakeProperty';

        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata
            ->method('hasAssociation')
            ->with($property)
            ->willReturn(false);

        $classMetadata->fieldMappings = [$property => ['fieldName' => $type]];
        $classMetadata
            ->method('getTypeOfField')
            ->with($property)
            ->willReturn($type);

        $this->documentManager
            ->method('getClassMetadata')
            ->willReturn($classMetadata);

        $result = $this->guesser->guessType($class, $property, $this->modelManager);

        $options = $result->getOptions();

        static::assertSame($resultType, $result->getType());
        static::assertSame($type, $options['field_name']);
        static::assertSame($confidence, $result->getConfidence());
        static::assertSame([], $options['options']);
        static::assertSame([], $options['field_options']);

        if ($fieldType) {
            static::assertSame($fieldType, $options['field_type']);
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
