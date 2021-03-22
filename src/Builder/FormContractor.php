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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\AbstractFormContractor;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class FormContractor extends AbstractFormContractor
{
    /**
     * @deprecated since version 3.1.0, to be removed in 4.0
     *
     * @var FormFactoryInterface
     */
    protected $fieldFactory;

    /**
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        // NEXT_MAJOR: Remove the following 2 lines.
        $modelManager = $admin->getModelManager();
        \assert($modelManager instanceof ModelManager);

        // NEXT_MAJOR: Remove this block.
        if ($modelManager->hasMetadata($admin->getClass(), 'sonata_deprecation_mute')) {
            $metadata = $modelManager->getMetadata($admin->getClass(), 'sonata_deprecation_mute');

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

        // NEXT_MAJOR: Remove this block.
        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf(
                'Please define a type for field `%s` in `%s`',
                $fieldDescription->getName(),
                \get_class($admin)
            ));
        }

        // NEXT_MAJOR: Remove this call.
        $fieldDescription->setAdmin($admin);

        parent::fixFieldDescription($admin, $fieldDescription);
    }

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
