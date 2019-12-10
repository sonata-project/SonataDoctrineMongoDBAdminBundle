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

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class TypeGuesser extends AbstractTypeGuesser
{
    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        if (!$ret = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return new TypeGuess('text', [], Guess::LOW_CONFIDENCE);
        }

        list($metadata, $propertyName, $parentAssociationMappings) = $ret;

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
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'array':
              return new TypeGuess('array', [], Guess::HIGH_CONFIDENCE);
            case Type::BOOL:
            case Type::BOOLEAN:
                return new TypeGuess('boolean', [], Guess::HIGH_CONFIDENCE);
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'datetime':
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'vardatetime':
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'datetimetz':
            case Type::TIMESTAMP:
                return new TypeGuess('datetime', [], Guess::HIGH_CONFIDENCE);
            case Type::DATE:
            // NEXT_MAJOR: Use only the constant when dropping support for doctrine/mongodb-odm 1.3.
            // case Type::DATE_IMMUTABLE:
            case 'date_immutable':
                return new TypeGuess('date', [], Guess::HIGH_CONFIDENCE);
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'decimal':
            case Type::FLOAT:
                return new TypeGuess('number', [], Guess::MEDIUM_CONFIDENCE);
            case Type::INTEGER:
            case Type::INT:
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'bigint':
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'smallint':
                return new TypeGuess('integer', [], Guess::MEDIUM_CONFIDENCE);
            case Type::STRING:
                return new TypeGuess('text', [], Guess::MEDIUM_CONFIDENCE);
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'text':
                return new TypeGuess('textarea', [], Guess::MEDIUM_CONFIDENCE);
            /* @deprecated This type was deprecated since version 3.x, to be removed in 4.0 */
            case 'time':
                return new TypeGuess('time', [], Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess('text', [], Guess::LOW_CONFIDENCE);
        }
    }
}
