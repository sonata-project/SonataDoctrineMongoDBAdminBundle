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

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Model\ModelManagerInterface;
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

        $options = array(
            'field_type'     => null,
            'field_options'  => array(),
            'options'        => array(),
        );

        list($metadata, $propertyName, $parentAssociationMappings) = $ret;

        $options['parent_association_mappings'] = $parentAssociationMappings;

        if ($metadata->hasAssociation($propertyName)) {
            $mapping = $metadata->fieldMappings[$propertyName];

            switch ($mapping['type']) {
                case ClassMetadataInfo::ONE:
                case ClassMetadataInfo::MANY:
                    //case ClassMetadataInfo::MANY_TO_ONE:
                    //case ClassMetadataInfo::MANY_TO_MANY:

                    $options['operator_type']    = 'sonata_type_equal';
                    $options['operator_options'] = array();

                    $options['field_type'] = 'document';
                    $options['field_options'] = array(
                        'class' => $mapping['targetDocument'],
                    );

                    $options['field_name'] = $mapping['fieldName'];
                    $options['mapping_type'] = $mapping['type'];

                    return new TypeGuess('doctrine_mongo_model', $options, Guess::HIGH_CONFIDENCE);
            }
        }

        $options['field_name'] = $metadata->fieldMappings[$propertyName]['fieldName'];

        switch ($metadata->getTypeOfField($propertyName)) {
            case 'boolean':
                $options['field_type'] = 'sonata_type_boolean';
                $options['field_options'] = array();

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
                $options['field_type'] = 'number';

                return new TypeGuess('doctrine_mongo_number', $options, Guess::MEDIUM_CONFIDENCE);
            case 'id':
            case 'string':
            case 'text':
                $options['field_type'] = 'text';

                return new TypeGuess('doctrine_mongo_string', $options, Guess::MEDIUM_CONFIDENCE);
            case 'time':
                return new TypeGuess('doctrine_mongo_time', $options, Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess('doctrine_mongo_string', $options, Guess::LOW_CONFIDENCE);
        }
    }
}
