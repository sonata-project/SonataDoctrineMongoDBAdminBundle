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
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Symfony\Component\Form\FormFactory;

class DatagridBuilder implements DatagridBuilderInterface
{
    protected $filterFactory;

    protected $formFactory;

    protected $guesser;

    /**
     * Indicates that csrf protection enabled.
     *
     * @var bool
     */
    protected $csrfTokenEnabled;

    /**
     * @param FormFactory            $formFactory
     * @param FilterFactoryInterface $filterFactory
     * @param TypeGuesserInterface   $guesser
     * @param bool                   $csrfTokenEnabled
     */
    public function __construct(FormFactory $formFactory, FilterFactoryInterface $filterFactory, TypeGuesserInterface $guesser, $csrfTokenEnabled = true)
    {
        $this->formFactory = $formFactory;
        $this->filterFactory = $filterFactory;
        $this->guesser = $guesser;
        $this->csrfTokenEnabled = $csrfTokenEnabled;
    }

    /**
     * @param AdminInterface            $admin
     * @param FieldDescriptionInterface $fieldDescription
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        // set default values
        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            list($metadata, $lastPropertyName, $parentAssociationMappings) = $admin->getModelManager()->getParentMetadataForProperty($admin->getClass(), $fieldDescription->getName());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$lastPropertyName])) {
                $fieldDescription->setOption('field_mapping', $fieldDescription->getOption('field_mapping', $metadata->fieldMappings[$lastPropertyName]));

                if ($metadata->fieldMappings[$lastPropertyName]['type'] == 'string') {
                    $fieldDescription->setOption('global_search', $fieldDescription->getOption('global_search', true)); // always search on string field only
                }
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$lastPropertyName])) {
                $fieldDescription->setOption('association_mapping', $fieldDescription->getOption('association_mapping', $metadata->associationMappings[$lastPropertyName]));
            }

            $fieldDescription->setOption('parent_association_mappings', $fieldDescription->getOption('parent_association_mappings', $parentAssociationMappings));
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('name', $fieldDescription->getOption('name', $fieldDescription->getName()));

        if (in_array($fieldDescription->getMappingType(), array(ClassMetadataInfo::ONE, ClassMetadataInfo::MANY, ClassMetadataInfo::REFERENCE_MANY, ClassMetadataInfo::REFERENCE_ONE ))) {
            $admin->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @param DatagridInterface      $datagrid
     * @param null                                                $type
     * @param FieldDescriptionInterface $fieldDescription
     * @param AdminInterface            $admin
     */
    public function addFilter(DatagridInterface $datagrid, $type, FieldDescriptionInterface $fieldDescription, AdminInterface $admin)
    {
        if ($type == null) {
            $guessType = $this->guesser->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());

            $type = $guessType->getType();

            $fieldDescription->setType($type);

            $options = $guessType->getOptions();

            foreach ($options as $name => $value) {
                if (is_array($value)) {
                    $fieldDescription->setOption($name, array_merge($value, $fieldDescription->getOption($name, array())));
                } else {
                    $fieldDescription->setOption($name, $fieldDescription->getOption($name, $value));
                }
            }
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);
        $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);

        $fieldDescription->mergeOption('field_options', array('required' => false));

        if ('doctrine_mongo_autocomplete' === $type) {
            $fieldDescription->mergeOption('field_options', array(
                'class' => $fieldDescription->getTargetEntity(),
                'model_manager' => $fieldDescription->getAdmin()->getModelManager(),
                'admin_code' => $admin->getCode(),
                'context' => 'filter',
            ));
        }

        $filter = $this->filterFactory->create($fieldDescription->getName(), $type, $fieldDescription->getOptions());

        if (false !== $filter->getLabel() && !$filter->getLabel()) {
            $filter->setLabel($admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'filter', 'label'));
        }

        $datagrid->addFilter($filter);
    }

    /**
     * @param AdminInterface $admin
     * @param array                                    $values
     *
     * @return DatagridInterface
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = array())
    {
        $pager = $this->getPager($admin->getPagerType());
        $pager->setCountColumn($admin->getModelManager()->getIdentifierFieldNames($admin->getClass()));

        $defaultOptions = array();
        if ($this->csrfTokenEnabled) {
            $defaultOptions['csrf_protection'] = false;
        }

        $formBuilder = $this->formFactory->createNamedBuilder('filter', 'form', array(), $defaultOptions);

        return new Datagrid($admin->createQuery(), $admin->getList(), $pager, $formBuilder, $values);
    }

    /**
     * Get pager by pagerType
     *
     * @param string $pagerType
     *
     * @return PagerInterface
     * @throws \RuntimeException If invalid pager type is set.
     */
    protected function getPager($pagerType)
    {
        switch ($pagerType) {
            case Pager::TYPE_DEFAULT:
                return new Pager();
            default:
                throw new \RuntimeException(sprintf('Unknown pager type "%s".', $pagerType));
        }
    }
}
