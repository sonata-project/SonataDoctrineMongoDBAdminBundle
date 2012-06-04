<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Guesser;

use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Doctrine\ODM\MongoDB\MongoDBException;


class TypeGuesser extends AbstractTypeGuesser
{
    /**
     * @param string $class
     * @param string $property
     * @return TypeGuess
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        if (!$ret = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }

        list($metadata, $propertyName, $parentAssociationMappings) = $ret;

        $mapping = $metadata->getFieldMapping($property);
        if ($metadata->hasAssociation($propertyName)) {
            $multiple = $metadata->isCollectionValuedAssociation($propertyName);

            switch ($mapping['type']) {
                case ClassMetadataInfo::ONE:
                    return new TypeGuess('orm_one_to_many', array(), Guess::HIGH_CONFIDENCE);

                case ClassMetadataInfo::MANY:
                    return new TypeGuess('orm_many_to_many', array(), Guess::HIGH_CONFIDENCE);

                /* case ClassMetadataInfo::MANY_TO_ONE:
                  return new TypeGuess('orm_many_to_one', array(), Guess::HIGH_CONFIDENCE);

                  case ClassMetadataInfo::ONE_TO_ONE:
                  return new TypeGuess('orm_one_to_one', array(), Guess::HIGH_CONFIDENCE); */
            }
        }

        switch ($mapping['type']) {
            case 'hash':
            case 'array':
              return new TypeGuess('array', array(), Guess::HIGH_CONFIDENCE);
            case 'boolean':
                return new TypeGuess('boolean', array(), Guess::HIGH_CONFIDENCE);
            case 'datetime':
            case 'vardatetime':
            case 'datetimetz':
            case 'timestamp':
                return new TypeGuess('datetime', array(), Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess('date', array(), Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'float':
                return new TypeGuess('number', array(), Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'bigint':
            case 'smallint':
                return new TypeGuess('integer', array(), Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess('text', array(), Guess::MEDIUM_CONFIDENCE);
            case 'text':
                return new TypeGuess('textarea', array(), Guess::MEDIUM_CONFIDENCE);
            case 'time':
                return new TypeGuess('time', array(), Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess('text', array(), Guess::LOW_CONFIDENCE);
        }
    }
}
