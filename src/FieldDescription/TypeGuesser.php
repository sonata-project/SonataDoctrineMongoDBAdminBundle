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

        // TODO: Remove Type::BOOLEAN and Type::INTEGER when dropping support of doctrine/mongodb-odm < 3.0
        return match ($fieldDescription->getMappingType()) {
            Type::COLLECTION, Type::HASH => new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE),
            Type::BOOL, Type::BOOLEAN => new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE),
            Type::TIMESTAMP => new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE),
            Type::DATE, Type::DATE_IMMUTABLE => new TypeGuess(FieldDescriptionInterface::TYPE_DATE, [], Guess::HIGH_CONFIDENCE),
            Type::FLOAT => new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::MEDIUM_CONFIDENCE),
            Type::INTEGER, Type::INT => new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE),
            Type::ID, Type::STRING => new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE),
            default => new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE),
        };
    }
}
