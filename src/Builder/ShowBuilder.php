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
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.x.
 */
class ShowBuilder implements ShowBuilderInterface
{
    protected $guesser;

    protected $templates;

    public function __construct(TypeGuesserInterface $guesser, array $templates)
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    public function getBaseList(array $options = [])
    {
        return new FieldDescriptionCollection();
    }

    public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if (null === $type) {
            $guessType = $this->guesser->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());
            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * The method defines the correct default settings for the provided FieldDescription.
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            [$metadata, $lastPropertyName, $parentAssociationMappings] = $admin->getModelManager()->getParentMetadataForProperty($admin->getClass(), $fieldDescription->getName());
            $fieldDescription->setParentAssociationMappings($parentAssociationMappings);

            // set the default field mapping
            if (isset($metadata->fieldMappings[$lastPropertyName])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$lastPropertyName]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$lastPropertyName])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$lastPropertyName]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), \get_class($admin)));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {
            if ('id' === $fieldDescription->getType()) {
                $fieldDescription->setType('string');
            }

            if ('int' === $fieldDescription->getType()) {
                $fieldDescription->setType('integer');
            }

            $template = $this->getTemplate($fieldDescription->getType());

            if (null === $template) {
                if (ClassMetadata::ONE === $fieldDescription->getMappingType()) {
                    $template = '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig';
                } elseif (ClassMetadata::MANY === $fieldDescription->getMappingType()) {
                    $template = '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig';
                }
            }

            $fieldDescription->setTemplate($template);
        }

        if (\in_array($fieldDescription->getMappingType(), [ClassMetadata::ONE, ClassMetadata::MANY], true)) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @param int|string $type
     */
    private function getTemplate($type): ?string
    {
        if (!isset($this->templates[$type])) {
            return null;
        }

        return $this->templates[$type];
    }
}
