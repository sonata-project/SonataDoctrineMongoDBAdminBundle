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
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class TypeGuesser implements TypeGuesserInterface
{
    /**
     * @psalm-suppress DeprecatedConstant
     */
    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        $fieldMapping = $fieldDescription->getFieldMapping();

        if ([] === $fieldMapping) {
            return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }

        if ([] !== $fieldDescription->getAssociationMapping()) {
            switch ($fieldDescription->getMappingType()) {
                case ClassMetadata::ONE:
                    return new TypeGuess(FieldDescriptionInterface::TYPE_MANY_TO_ONE, [], Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY:
                    return new TypeGuess(FieldDescriptionInterface::TYPE_MANY_TO_MANY, [], Guess::HIGH_CONFIDENCE);
            }
        }

        switch ($fieldDescription->getMappingType()) {
            case Type::COLLECTION:
            case Type::HASH:
                return new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE);
            case Type::BOOL:
            // TODO: Remove it when dropping support of doctrine/mongodb-odm < 3.0
            case Type::BOOLEAN:
                return new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case Type::TIMESTAMP:
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case Type::DATE:
            case Type::DATE_IMMUTABLE:
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATE, [], Guess::HIGH_CONFIDENCE);
            case Type::FLOAT:
                return new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::MEDIUM_CONFIDENCE);
            // TODO: Remove it when dropping support of doctrine/mongodb-odm < 3.0
            case Type::INTEGER:
            case Type::INT:
                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case Type::ID:
            case Type::STRING:
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }
    }
}
