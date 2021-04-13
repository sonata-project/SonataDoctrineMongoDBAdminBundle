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

namespace Sonata\DoctrineMongoDBAdminBundle\FieldDescription;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;
use Sonata\DoctrineMongoDBAdminBundle\Model\MissingPropertyMetadataException;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class FilterTypeGuesser implements TypeGuesserInterface
{
    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        $options = [
            'parent_association_mappings' => $fieldDescription->getParentAssociationMappings(),
            'field_name' => $fieldDescription->getFieldName(),
            'field_type' => null,
            'field_options' => [],
            'options' => [],
        ];

        if ([] !== $fieldDescription->getAssociationMapping()) {
            switch ($fieldDescription->getMappingType()) {
                case ClassMetadata::ONE:
                case ClassMetadata::MANY:
                    $options['operator_type'] = EqualOperatorType::class;
                    $options['operator_options'] = [];
                    $options['field_type'] = DocumentType::class;
                    $options['field_options'] = [
                        'class' => $fieldDescription->getTargetModel(),
                    ];
                    $options['field_name'] = $fieldDescription->getFieldName();
                    $options['mapping_type'] = $fieldDescription->getMappingType();

                    return new TypeGuess(ModelFilter::class, $options, Guess::HIGH_CONFIDENCE);
            }
        }

        if ([] === $fieldDescription->getFieldMapping()) {
            throw new MissingPropertyMetadataException(
                $fieldDescription->getAdmin()->getClass(),
                $fieldDescription->getFieldName()
            );
        }

        switch ($fieldDescription->getMappingType()) {
            case Type::BOOL:
            case Type::BOOLEAN:
                $options['field_type'] = BooleanType::class;
                $options['field_options'] = [];

                return new TypeGuess(BooleanFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::TIMESTAMP:
                $options['field_type'] = DateTimeType::class;

                return new TypeGuess(DateTimeFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::DATE:

            case Type::DATE_IMMUTABLE:
                $options['field_type'] = DateType::class;

                return new TypeGuess(DateFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::FLOAT:
            case Type::INT:
            case Type::INTEGER:
                $options['field_type'] = NumberType::class;

                return new TypeGuess(NumberFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            case Type::ID:
            case Type::STRING:
                $options['field_type'] = TextType::class;

                return new TypeGuess(StringFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(StringFilter::class, $options, Guess::LOW_CONFIDENCE);
        }
    }
}
