<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) 2010-2011 Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Admin;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Admin\BaseFieldDescription;

class FieldDescription extends BaseFieldDescription
{
    public function __construct()
    {
        $this->parentAssociationMappings = array();
    }

    /**
     * Define the association mapping definition
     *
     * @param array $associationMapping
     * @return void
     */
    public function setAssociationMapping($associationMapping)
    {
        if (!is_array($associationMapping)) {
            throw new \RuntimeException('The association mapping must be an array');
        }

        $this->associationMapping = $associationMapping;

        $this->type = $this->type ? : $associationMapping['type'];
        if (!$this->mappingType) {
            switch ($associationMapping['type']) {
                case ClassMetadataInfo::MANY:
                    $this->mappingType = ClassMetadataInfo::MANY;
                    break;
                case ClassMetadataInfo::ONE:
                    $this->mappingType = ClassMetadataInfo::ONE;
                    break;
            }
        }
        
        $this->fieldName = $associationMapping['fieldName'];
    }

    /**
     * return the related Target Entity
     *
     * @return string|null
     */
    public function getTargetEntity()
    {
        if ($this->associationMapping) {
            return $this->associationMapping['targetDocument'];
        }

        return null;
    }

    /**
     * set the field mapping information
     *
     * @param array $fieldMapping
     * @return void
     */
    public function setFieldMapping($fieldMapping)
    {
        if (!is_array($fieldMapping)) {
            throw new \RuntimeException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;

        $this->type = $this->type ? : $fieldMapping['type'];
        $this->mappingType = $this->mappingType ? : $fieldMapping['type'];
        $this->fieldName = $this->fieldName ? : $fieldMapping['fieldName'];
    }

    /**
     * return true if the FieldDescription is linked to an identifier field
     *
     * @return bool
     */
    public function isIdentifier()
    {
        return isset($this->fieldMapping['id']) ? $this->fieldMapping['id'] : false;
    }

    /**
     * return the value linked to the description
     *
     * @param  $object
     * @return bool|mixed
     */
    public function getValue($object)
    {
        if($this->parentAssociationMappings) {
            foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
                $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
            }
        }

        return $this->getFieldValue($object, $this->fieldName);
    }

    public function setParentAssociationMappings(array $parentAssociationMappings)
    {
        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            if (!is_array($parentAssociationMapping)) {
                throw new \RuntimeException('An association mapping must be an array');
            }
        }

        $this->parentAssociationMappings = $parentAssociationMappings;
    }

}
