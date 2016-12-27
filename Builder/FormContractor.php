<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Builder;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Symfony\Component\Form\FormFactoryInterface;

class FormContractor implements FormContractorInterface
{
    protected $fieldFactory;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin)));
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));

        if (in_array($fieldDescription->getMappingType(), array(ClassMetadataInfo::ONE, ClassMetadataInfo::MANY))) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormBuilder($name, array $options = array())
    {
        return $this->getFormFactory()->createNamedBuilder($name, 'form', null, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        $options = array();
        $options['sonata_field_description'] = $fieldDescription;

        // NEXT_MAJOR: Check only against FQCNs when dropping support for Symfony <2.8
        if ($this->checkFormType($type, array(
            'sonata_type_model',
            'sonata_type_model_list',
        )) || $this->checkFormClass($type, array(
            'Sonata\AdminBundle\Form\Type\ModelType',
            'Sonata\AdminBundle\Form\Type\ModelListType',
        ))) {
            if ($fieldDescription->getOption('edit') == 'list') {
                throw new \LogicException('The ``sonata_type_model`` type does not accept an ``edit`` option anymore, please review the UPGRADE-2.1.md file from the SonataAdminBundle');
            }

            $options['class'] = $fieldDescription->getTargetEntity();
            $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();
            // NEXT_MAJOR: Check only against FQCNs when dropping support for Symfony <2.8
        } elseif ($this->checkFormType($type, array('sonata_type_admin')) || $this->checkFormClass($type, array('Sonata\AdminBundle\Form\Type\AdminType'))) {
            if (!$fieldDescription->getAssociationAdmin()) {
                throw new \RuntimeException(sprintf('The current field `%s` is not linked to an admin. Please create one for the target entity : `%s`', $fieldDescription->getName(), $fieldDescription->getTargetEntity()));
            }

            $options['data_class'] = $fieldDescription->getAssociationAdmin()->getClass();
            $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'admin'));
            // NEXT_MAJOR: Check only against FQCNs when dropping support for Symfony <2.8
        } elseif ($this->checkFormType($type, array('sonata_type_collection')) || $this->checkFormClass($type, array('Sonata\CoreBundle\Form\Type\CollectionType'))) {
            if (!$fieldDescription->getAssociationAdmin()) {
                throw new \RuntimeException(sprintf('The current field `%s` is not linked to an admin. Please create one for the target entity : `%s`', $fieldDescription->getName(), $fieldDescription->getTargetEntity()));
            }

            // NEXT_MAJOR: Use only FQCN when dropping support for Symfony <2.8
            $options['type'] = 'sonata_type_collection' === $type ?
                'sonata_type_admin' :
                'Sonata\AdminBundle\Form\Type\AdminType'
            ;
            $options['modifiable'] = true;
            $options['type_options'] = array(
                'sonata_field_description' => $fieldDescription,
                'data_class' => $fieldDescription->getAssociationAdmin()->getClass(),
            );
        }

        return $options;
    }

    /**
     * NEXT_MAJOR: See next major comments above, this method should be removed when dropping support for Symfony <2.8.
     *
     * @param string $type
     * @param array  $types
     *
     * @return bool
     */
    private function checkFormType($type, $types)
    {
        return in_array($type, $types, true);
    }

    /**
     * @param string $type
     * @param array  $classes
     *
     * @return array
     */
    private function checkFormClass($type, $classes)
    {
        return array_filter($classes, function ($subclass) use ($type) {
            return is_a($type, $subclass, true);
        });
    }
}
