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

namespace Sonata\DoctrineMongoDBAdminBundle\Guesser;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
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

final class FilterTypeGuesser extends AbstractTypeGuesser
{
    public function guessType(string $class, string $property, ModelManagerInterface $modelManager): ?TypeGuess
    {
        if (!$ret = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return null;
        }

        $options = [
            'field_type' => null,
            'field_options' => [],
            'options' => [],
        ];

        [$metadata, $propertyName, $parentAssociationMappings] = $ret;

        $options['parent_association_mappings'] = $parentAssociationMappings;

        if ($metadata->hasAssociation($propertyName)) {
            $mapping = $metadata->fieldMappings[$propertyName];

            switch ($mapping['type']) {
                case ClassMetadata::ONE:
                case ClassMetadata::MANY:
                    $options['operator_type'] = EqualOperatorType::class;
                    $options['operator_options'] = [];

                    $options['field_type'] = DocumentType::class;
                    $options['field_options'] = [
                        'class' => $mapping['targetDocument'],
                    ];

                    $options['field_name'] = $mapping['fieldName'];
                    $options['mapping_type'] = $mapping['type'];

                    return new TypeGuess(ModelFilter::class, $options, Guess::HIGH_CONFIDENCE);
            }
        }

        if (!\array_key_exists($propertyName, $metadata->fieldMappings)) {
            throw new MissingPropertyMetadataException($class, $property);
        }

        $options['field_name'] = $metadata->fieldMappings[$propertyName]['fieldName'];

        switch ($metadata->getTypeOfField($propertyName)) {
            case Type::BOOL:
            case Type::BOOLEAN:
                $options['field_type'] = BooleanType::class;
                $options['field_options'] = [];

                return new TypeGuess(BooleanFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case 'datetime':
                @trigger_error(
                    'The datetime type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                $options['field_type'] = DateTimeType::class;

                return new TypeGuess(DateTimeFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::TIMESTAMP:
                $options['field_type'] = DateTimeType::class;

                return new TypeGuess(DateTimeFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case Type::DATE:

            case Type::DATE_IMMUTABLE:
                $options['field_type'] = DateType::class;

                return new TypeGuess(DateFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case 'decimal':
                @trigger_error(
                    'The decimal type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                $options['field_type'] = NumberType::class;

                return new TypeGuess(NumberFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            case 'bigint':
                @trigger_error(
                    'The bigint type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                $options['field_type'] = NumberType::class;

                return new TypeGuess(NumberFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            case 'smallint':
                @trigger_error(
                    'The smallint type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                $options['field_type'] = NumberType::class;

                return new TypeGuess(NumberFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            case Type::FLOAT:
            case Type::INT:
            case Type::INTEGER:
                $options['field_type'] = NumberType::class;

                return new TypeGuess(NumberFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            case 'text':
                @trigger_error(
                    'The text type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                $options['field_type'] = TextType::class;

                return new TypeGuess(StringFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            case Type::ID:
            case Type::STRING:
                $options['field_type'] = TextType::class;

                return new TypeGuess(StringFilter::class, $options, Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(StringFilter::class, $options, Guess::LOW_CONFIDENCE);
        }
    }
}
