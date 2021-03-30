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
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class FieldDescription extends BaseFieldDescription
{
    public function getTargetModel(): ?string
    {
        if ($this->associationMapping) {
            return $this->associationMapping['targetDocument'];
        }

        return null;
    }

    public function isIdentifier(): bool
    {
        return $this->fieldMapping['id'] ?? false;
    }

    public function getValue($object)
    {
        foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
            $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
        }

        return $this->getFieldValue($object, $this->fieldName);
    }

    public function describesSingleValuedAssociation(): bool
    {
        return \in_array($this->mappingType, [
            ClassMetadata::ONE,
            ClassMetadata::REFERENCE_ONE,
            ClassMetadata::EMBED_ONE,
        ], true);
    }

    public function describesCollectionValuedAssociation(): bool
    {
        return \in_array($this->mappingType, [
            ClassMetadata::MANY,
            ClassMetadata::REFERENCE_MANY,
            ClassMetadata::EMBED_MANY,
        ], true);
    }

    protected function setAssociationMapping(array $associationMapping): void
    {
        $this->associationMapping = $associationMapping;

        // NEXT_MAJOR: Remove next line.
        $this->type = $this->type ?: $associationMapping['type'];
        $this->mappingType = $this->mappingType ?: $associationMapping['type'];
        $this->fieldName = $associationMapping['fieldName'];
    }

    protected function setFieldMapping(array $fieldMapping): void
    {
        $this->fieldMapping = $fieldMapping;

        // NEXT_MAJOR: Remove next line.
        $this->type = $this->type ?: $fieldMapping['type'];
        $this->mappingType = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    protected function setParentAssociationMappings(array $parentAssociationMappings): void
    {
        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            if (!\is_array($parentAssociationMapping)) {
                throw new \RuntimeException('An association mapping must be an array');
            }
        }

        $this->parentAssociationMappings = $parentAssociationMappings;
    }
}
