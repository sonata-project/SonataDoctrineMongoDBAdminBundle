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

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescriptionFactory;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;
use Sonata\DoctrineMongoDBAdminBundle\Model\MissingPropertyMetadataException;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\AssociatedDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\ContainerDocument;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;

final class FilterTypeGuesserTest extends RegistryTestCase
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
        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $fieldDescription
            ->method('getAssociationMapping')
            ->willReturn([]);

        $fieldDescription
            ->method('getFieldMapping')
            ->willReturn([]);

        $fieldDescription
            ->method('getFieldName')
            ->willReturn('nonExisting');

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $fieldDescription
            ->method('getAdmin')
            ->willReturn($admin);

        $this->expectException(MissingPropertyMetadataException::class);
        $this->guesser->guess($fieldDescription);
    }

    public function testGuessTypeWithAssociation(): void
    {
        $className = ContainerDocument::class;
        $property = 'associatedDocument';
        $parentAssociation = [];
        $targetDocument = AssociatedDocument::class;

        $fieldDescriptionFactory = new FieldDescriptionFactory($this->registry);

        $fieldDescription = $fieldDescriptionFactory->create($className, $property);

        $result = $this->guesser->guess($fieldDescription);

        $options = $result->getOptions();

        $this->assertSame(ModelFilter::class, $result->getType());
        $this->assertSame(Guess::HIGH_CONFIDENCE, $result->getConfidence());
        $this->assertSame($parentAssociation, $options['parent_association_mappings']);
        $this->assertSame(ClassMetadata::ONE, $options['mapping_type']);
        $this->assertSame(EqualOperatorType::class, $options['operator_type']);
        $this->assertSame([], $options['operator_options']);
        $this->assertSame($property, $options['field_name']);
        $this->assertSame(DocumentType::class, $options['field_type']);
        $this->assertSame($targetDocument, $options['field_options']['class']);
    }

    /**
     * @dataProvider noAssociationData
     */
    public function testGuessTypeNoAssociation(string $type, string $resultType, int $confidence, ?string $fieldType = null): void
    {
        $property = 'fakeProperty';

        $fieldDescription = $this->createStub(FieldDescriptionInterface::class);
        $fieldDescription
           ->method('getMappingType')
           ->willReturn($type);

        $fieldDescription
           ->method('getFieldName')
           ->willReturn($property);

        $fieldDescription
           ->method('getFieldMapping')
           ->willReturn([$property => ['fieldName' => $property]]);

        $fieldDescription
           ->method('getAssociationMapping')
           ->willReturn([]);

        $result = $this->guesser->guess($fieldDescription);

        $options = $result->getOptions();

        $this->assertSame($resultType, $result->getType());
        $this->assertSame($confidence, $result->getConfidence());
        $this->assertSame([], $options['options']);
        $this->assertSame([], $options['field_options']);

        if ($fieldType) {
            $this->assertSame($fieldType, $options['field_type']);
        }
    }

    public function noAssociationData(): iterable
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
