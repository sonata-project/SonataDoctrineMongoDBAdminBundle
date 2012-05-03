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

namespace Sonata\DoctrineMongoDBAdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;

class ShowBuilder implements ShowBuilderInterface
{
    protected $guesser;

    public function __construct(TypeGuesserInterface $guesser)
    {
        $this->guesser = $guesser;
    }

    public function getBaseList(array $options = array())
    {
        return new FieldDescriptionCollection;
    }

    public function addField(FieldDescriptionCollection $list, $type = null, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type == null) {
            $guessType = $this->guesser->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());
            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        switch ($fieldDescription->getMappingType()) {
            default:
                $list->add($fieldDescription);
        }
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param \Sonata\AdminBundle\Admin\FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);

                // set the default association mapping
                if (isset($metadata->fieldMappings[$fieldDescription->getName()]['reference'])) {
                    $fieldDescription->setAssociationMapping($metadata->fieldMappings[$fieldDescription->getName()]);
                }
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate(sprintf('SonataAdminBundle:CRUD:show_%s.html.twig', $fieldDescription->getType()));

            if ($fieldDescription->getMappingType() == ClassMetadataInfo::MANY) {
                $fieldDescription->setTemplate('SonataDoctrineMongoDBAdminBundle:CRUD:show_mongo_many.html.twig');
            }

            if ($fieldDescription->getMappingType() == ClassMetadataInfo::ONE) {
                $fieldDescription->setTemplate('SonataDoctrineMongoDBAdminBundle:CRUD:show_mongo_one.html.twig');
            }
        }

        if ($fieldDescription->getMappingType() == ClassMetadataInfo::MANY) {
            $admin->attachAdminClass($fieldDescription);
        }

        if ($fieldDescription->getMappingType() == ClassMetadataInfo::ONE) {
            $admin->attachAdminClass($fieldDescription);
        }
    }
}