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
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Sonata\CoreBundle\Form\Type\EqualType;
use Sonata\DoctrineMongoDBAdminBundle\Model\MissingPropertyMetadataException;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class FilterTypeGuesser extends AbstractTypeGuesser
{
    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property, ModelManagerInterface $modelManager)
    {
        if (!$ret = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return false;
        }

        $options = [
            'field_type' => null,
            'field_options' => [],
            'options' => [],
        ];

        list($metadata, $propertyName, $parentAssociationMappings) = $ret;

        $options['parent_association_mappings'] = $parentAssociationMappings;

        if ($metadata->hasAssociation($propertyName)) {
            $mapping = $metadata->fieldMappings[$propertyName];

            switch ($mapping['type']) {
                case ClassMetadata::ONE:
                case ClassMetadata::MANY:
                    //case ClassMetadata::MANY_TO_ONE:
                    //case ClassMetadata::MANY_TO_MANY:

                    $options['operator_type'] = EqualType::class;
                    $options['operator_options'] = [];

                    $options['field_type'] = DocumentType::class;
                    $options['field_options'] = [
                        'class' => $mapping['targetDocument'],
                    ];

                    $options['field_name'] = $mapping['fieldName'];
                    $options['mapping_type'] = $mapping['type'];

                    return new TypeGuess('doctrine_mongo_model', $options, Guess::HIGH_CONFIDENCE);
            }
        }

        if (!array_key_exists($propertyName, $metadata->fieldMappings)) {
            throw new MissingPropertyMetadataException($class, $property);
        }

        $options['field_name'] = $metadata->fieldMappings[$propertyName]['fieldName'];

        switch ($metadata->getTypeOfField($propertyName)) {
            case 'boolean':
                $options['field_type'] = BooleanType::class;
                $options['field_options'] = [];

                return new TypeGuess('doctrine_mongo_boolean', $options, Guess::HIGH_CONFIDENCE);
//            case 'datetime':
//            case 'vardatetime':
//            case 'datetimetz':
//                return new TypeGuess('doctrine_orm_datetime', $options, Guess::HIGH_CONFIDENCE);
//            case 'date':
//                return new TypeGuess('doctrine_orm_date', $options, Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'float':
                return new TypeGuess('doctrine_mongo_number', $options, Guess::MEDIUM_CONFIDENCE);
            case 'int':
            case 'bigint':
            case 'smallint':
                $options['field_type'] = NumberType::class;

                return new TypeGuess('doctrine_mongo_number', $options, Guess::MEDIUM_CONFIDENCE);
            case 'id':
            case 'string':
            case 'text':
                $options['field_type'] = TextType::class;

                return new TypeGuess('doctrine_mongo_string', $options, Guess::MEDIUM_CONFIDENCE);
            case 'time':
                return new TypeGuess('doctrine_mongo_time', $options, Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess('doctrine_mongo_string', $options, Guess::LOW_CONFIDENCE);
        }
    }
}
