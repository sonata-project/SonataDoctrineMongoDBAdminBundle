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
use Sonata\AdminBundle\Builder\ListBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface as DeprecatedTypeGuesserInterface;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class ListBuilder implements ListBuilderInterface
{
    /**
     * NEXT_MAJOR: Remove DeprecatedTypeGuesserInterface type.
     *
     * @var DeprecatedTypeGuesserInterface|TypeGuesserInterface
     */
    protected $guesser;

    /**
     * @var string[]
     */
    protected $templates = [];

    /**
     * NEXT_MAJOR: Remove DeprecatedTypeGuesserInterface type and add TypeGuesserInterface to the constructor.
     *
     * @param DeprecatedTypeGuesserInterface|TypeGuesserInterface $guesser
     * @param string[]                                            $templates
     */
    public function __construct($guesser, array $templates = [])
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    public function getBaseList(array $options = [])
    {
        return new FieldDescriptionCollection();
    }

    /**
     * @return void
     */
    public function buildField($type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if (null === $type) {
            // NEXT_MAJOR: Remove the condition and keep the if part.
            if ($this->guesser instanceof TypeGuesserInterface) {
                $guessType = $this->guesser->guess($fieldDescription);
            } else {
                $guessType = $this->guesser->guessType(
                    $admin->getClass(),
                    $fieldDescription->getName(),
                    $admin->getModelManager()
                );
            }
            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
    }

    /**
     * @return void
     */
    public function addField(FieldDescriptionCollection $list, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        $this->buildField($type, $fieldDescription, $admin);
        $admin->addListFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * @return void
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        if ('_action' === $fieldDescription->getName() || 'actions' === $fieldDescription->getType()) {
            $this->buildActionFieldDescription($fieldDescription, 'sonata_deprecation_mute');
        }

        // NEXT_MAJOR: Remove this call.
        $fieldDescription->setAdmin($admin);

        // NEXT_MAJOR: Remove the following 2 lines.
        $modelManager = $admin->getModelManager();
        \assert($modelManager instanceof ModelManager);

        // NEXT_MAJOR: Remove this block.
        if ($modelManager->hasMetadata($admin->getClass(), 'sonata_deprecation_mute')) {
            [$metadata, $lastPropertyName, $parentAssociationMappings] = $modelManager->getParentMetadataForProperty($admin->getClass(), $fieldDescription->getName(), 'sonata_deprecation_mute');
            $fieldDescription->setParentAssociationMappings($parentAssociationMappings);

            // set the default field mapping
            if (isset($metadata->fieldMappings[$lastPropertyName])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$lastPropertyName]);
                if (false !== $fieldDescription->getOption('sortable')) {
                    $fieldDescription->setOption('sortable', $fieldDescription->getOption('sortable', true));
                    $fieldDescription->setOption('sort_parent_association_mappings', $fieldDescription->getOption('sort_parent_association_mappings', $fieldDescription->getParentAssociationMappings()));
                    $fieldDescription->setOption('sort_field_mapping', $fieldDescription->getOption('sort_field_mapping', $fieldDescription->getFieldMapping()));
                }
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$lastPropertyName])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$lastPropertyName]);
            }

            $fieldDescription->setOption('_sort_order', $fieldDescription->getOption('_sort_order', 'ASC'));
        }

        // NEXT_MAJOR: Uncomment this code.
        //if ([] !== $fieldDescription->getFieldMapping()) {
        //    if (false !== $fieldDescription->getOption('sortable')) {
        //        $fieldDescription->setOption('sortable', $fieldDescription->getOption('sortable', true));
        //        $fieldDescription->setOption('sort_parent_association_mappings', $fieldDescription->getOption('sort_parent_association_mappings', $fieldDescription->getParentAssociationMappings()));
        //        $fieldDescription->setOption('sort_field_mapping', $fieldDescription->getOption('sort_field_mapping', $fieldDescription->getFieldMapping()));
        //    }
        //
        //    $fieldDescription->setOption('_sort_order', $fieldDescription->getOption('_sort_order', 'ASC'));
        //}

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), \get_class($admin)));
        }

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
                    $template = '@SonataAdmin/CRUD/Association/list_many_to_one.html.twig';
                } elseif (ClassMetadata::MANY === $fieldDescription->getMappingType()) {
                    $template = '@SonataAdmin/CRUD/Association/list_many_to_many.html.twig';
                }
            }

            $fieldDescription->setTemplate($template);
        }

        if (\in_array($fieldDescription->getMappingType(), [ClassMetadata::ONE, ClassMetadata::MANY], true)) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @return \Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface
     */
    public function buildActionFieldDescription(FieldDescriptionInterface $fieldDescription)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and'
                .' will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        if (null === $fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate('@SonataAdmin/CRUD/list__action.html.twig');
        }

        if (null === $fieldDescription->getType()) {
            $fieldDescription->setType('actions');
        }

        if (null === $fieldDescription->getOption('name')) {
            $fieldDescription->setOption('name', 'Action');
        }

        if (null !== $fieldDescription->getOption('actions')) {
            $actions = $fieldDescription->getOption('actions');
            foreach ($actions as $k => $action) {
                if (!isset($action['template'])) {
                    $actions[$k]['template'] = sprintf('@SonataAdmin/CRUD/list__action_%s.html.twig', $k);
                }
            }

            $fieldDescription->setOption('actions', $actions);
        }

        return $fieldDescription;
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
