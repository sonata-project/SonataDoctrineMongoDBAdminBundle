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

namespace Sonata\DoctrineMongoDBAdminBundle\Builder;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Sonata\AdminBundle\Builder\AbstractFormContractor;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class FormContractor extends AbstractFormContractor
{
    // NEXT_MAJOR: Remove this method.
    protected function hasAssociation(FieldDescriptionInterface $fieldDescription): bool
    {
        if (method_exists($fieldDescription, 'describesAssociation')) {
            return $fieldDescription->describesAssociation();
        }

        return \in_array($fieldDescription->getMappingType(), [
            ClassMetadata::ONE,
            ClassMetadata::REFERENCE_ONE,
            ClassMetadata::EMBED_ONE,
            ClassMetadata::MANY,
            ClassMetadata::REFERENCE_MANY,
            ClassMetadata::EMBED_MANY,
        ], true);
    }

    // NEXT_MAJOR: Remove this method.
    protected function hasSingleValueAssociation(FieldDescriptionInterface $fieldDescription): bool
    {
        if (method_exists($fieldDescription, 'describesSingleValuedAssociation')) {
            return $fieldDescription->describesSingleValuedAssociation();
        }

        return \in_array($fieldDescription->getMappingType(), [
            ClassMetadata::ONE,
            ClassMetadata::REFERENCE_ONE,
            ClassMetadata::EMBED_ONE,
        ], true);
    }
}
