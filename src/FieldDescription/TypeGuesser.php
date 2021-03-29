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

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\DoctrineMongoDBAdminBundle\Guesser\AbstractTypeGuesser;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * NEXT_MAJOR: Remove extending from AbstractTypeGuesser.
 *
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class TypeGuesser extends AbstractTypeGuesser implements TypeGuesserInterface
{
    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and'
            .' will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        if (!$ret = $this->getParentMetadataForProperty($class, $property, $modelManager, 'sonata_deprecation_mute')) {
            // NEXT_MAJOR: Remove next line and uncomment the other following one.
            return new TypeGuess('text', [], Guess::LOW_CONFIDENCE);
            // return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }

        [$metadata, $propertyName, $parentAssociationMappings] = $ret;

        if ($metadata->hasAssociation($propertyName)) {
            $mapping = $metadata->fieldMappings[$propertyName];

            switch ($mapping['type']) {
                case ClassMetadata::ONE:
                    return new TypeGuess('mongo_one', [], Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY:
                    return new TypeGuess('mongo_many', [], Guess::HIGH_CONFIDENCE);
            }
        }

        switch ($metadata->getTypeOfField($propertyName)) {
            case Type::COLLECTION:
            case Type::HASH:
                return new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE);
            case 'array':
                @trigger_error(
                    'The array type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE);
            case Type::BOOL:
            case Type::BOOLEAN:
                return new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case 'datetime':
                @trigger_error(
                    'The datetime type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case 'vardatetime':
                @trigger_error(
                    'The vardatetime type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case 'datetimetz':
                @trigger_error(
                    'The datetimetz type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case Type::TIMESTAMP:
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case Type::DATE:
            case Type::DATE_IMMUTABLE:
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATE, [], Guess::HIGH_CONFIDENCE);
            case 'decimal':
                @trigger_error(
                    'The decimal type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess('number', [], Guess::MEDIUM_CONFIDENCE);
            case Type::FLOAT:
                // NEXT_MAJOR: Remove next line and uncomment the following one.
                return new TypeGuess('number', [], Guess::MEDIUM_CONFIDENCE);
                // return new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::MEDIUM_CONFIDENCE);
            case Type::INTEGER:
            case Type::INT:
                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case 'bigint':
                @trigger_error(
                    'The bigint type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case 'smallint':
                @trigger_error(
                    'The smallint type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case Type::STRING:
                // NEXT_MAJOR: Remove next line and uncomment the following one.
                return new TypeGuess('text', [], Guess::MEDIUM_CONFIDENCE);
                // return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            case 'text':
                @trigger_error(
                    'The text type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess('textarea', [], Guess::MEDIUM_CONFIDENCE);
            case 'time':
                @trigger_error(
                    'The time type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess('time', [], Guess::HIGH_CONFIDENCE);
            default:
                // NEXT_MAJOR: Remove next line and uncomment the following one.
                return new TypeGuess('text', [], Guess::LOW_CONFIDENCE);
                // return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
        }
    }

    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        $fieldMapping = $fieldDescription->getFieldMapping();

        if ([] === $fieldMapping) {
            // NEXT_MAJOR: Remove next line and uncomment the other following one.
            return new TypeGuess('text', [], Guess::LOW_CONFIDENCE);
            // return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }

        if ([] !== $fieldDescription->getAssociationMapping()) {
            switch ($fieldDescription->getMappingType()) {
                case ClassMetadata::ONE:
                    return new TypeGuess('mongo_one', [], Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY:
                    return new TypeGuess('mongo_many', [], Guess::HIGH_CONFIDENCE);
            }
        }

        switch ($fieldDescription->getMappingType()) {
            case Type::COLLECTION:
            case Type::HASH:
                return new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE);
            case 'array':
                @trigger_error(
                    'The array type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE);
            case Type::BOOL:
            case Type::BOOLEAN:
                return new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case 'datetime':
                @trigger_error(
                    'The datetime type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case 'vardatetime':
                @trigger_error(
                    'The vardatetime type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case 'datetimetz':
                @trigger_error(
                    'The datetimetz type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case Type::TIMESTAMP:
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case Type::DATE:
            case Type::DATE_IMMUTABLE:
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATE, [], Guess::HIGH_CONFIDENCE);
            case 'decimal':
                @trigger_error(
                    'The decimal type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess('number', [], Guess::MEDIUM_CONFIDENCE);
            case Type::FLOAT:
                // NEXT_MAJOR: Remove next line and uncomment the following one.
                return new TypeGuess('number', [], Guess::MEDIUM_CONFIDENCE);
            // return new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::MEDIUM_CONFIDENCE);
            case Type::INTEGER:
            case Type::INT:
                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case 'bigint':
                @trigger_error(
                    'The bigint type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case 'smallint':
                @trigger_error(
                    'The smallint type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case Type::STRING:
                // NEXT_MAJOR: Remove next line and uncomment the following one.
                return new TypeGuess('text', [], Guess::MEDIUM_CONFIDENCE);
            // return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            case 'text':
                @trigger_error(
                    'The text type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess('textarea', [], Guess::MEDIUM_CONFIDENCE);
            case 'time':
                @trigger_error(
                    'The time type is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
                    \E_USER_DEPRECATED
                );

                return new TypeGuess('time', [], Guess::HIGH_CONFIDENCE);
            default:
                // NEXT_MAJOR: Remove next line and uncomment the following one.
                return new TypeGuess('text', [], Guess::LOW_CONFIDENCE);
            // return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
        }
    }
}

// NEXT_MAJOR: Remove next line.
class_exists(\Sonata\DoctrineMongoDBAdminBundle\Guesser\TypeGuesser::class);
