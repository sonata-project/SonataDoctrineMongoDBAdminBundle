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
use Symfony\Component\Form\FormFactoryInterface;

final class FormContractor extends AbstractFormContractor
{
    /**
     * @deprecated since version 3.1.0, to be removed in 4.0
     *
     * @var FormFactoryInterface
     */
    private $fieldFactory;

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
