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

namespace Sonata\DoctrineMongoDBAdminBundle\Model;

use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;

use Symfony\Component\Form\Exception\PropertyAccessDeniedException;

use Exporter\Source\DoctrineODMQuerySourceIterator;

class ModelManager implements ModelManagerInterface
{
    protected $documentManager;

    /**
     *
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($class)
    {
        return $this->documentManager->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * Returns the model's metadata holding the fully qualified property, and the last
     * property name
     *
     * @param string $baseClass        The base class of the model holding the fully qualified property.
     * @param string $propertyFullName The name of the fully qualified property (dot ('.') separated
     * property string)
     * @return array(
     *     \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $parentMetadata,
     *     string $lastPropertyName,
     *     array $parentAssociationMappings
     * )
     */
    public function getParentMetadataForProperty($baseClass, $propertyFullName)
    {
        $nameElements = explode('.', $propertyFullName);
        $lastPropertyName = array_pop($nameElements);
        $class = $baseClass;
        $parentAssociationMappings = array();

        foreach ($nameElements as $nameElement) {
            $metadata = $this->getMetadata($class);
            $parentAssociationMappings[] = $metadata->fieldMappings[$nameElement];
            $class = $metadata->fieldMappings[$nameElement]['targetDocument'];
        }

        return array($this->getMetadata($class), $lastPropertyName, $parentAssociationMappings);
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadata($class)
    {
        return $this->documentManager->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = array())
    {
        if (!is_string($name)) {
            throw new \RunTimeException('The name argument must be a string');
        }

        list($metadata, $propertyName, $parentAssociationMappings) = $this->getParentMetadataForProperty($class, $name);

        $fieldDescription = new FieldDescription;
        $fieldDescription->setName($name);
        $fieldDescription->setOptions($options);
        $fieldDescription->setParentAssociationMappings($parentAssociationMappings);

        if ($metadata->hasAssociation($propertyName)) {
            $fieldDescription->setAssociationMapping($metadata->fieldMappings[$propertyName]);
        } elseif (isset($metadata->fieldMappings[$propertyName])) {
            $fieldDescription->setFieldMapping($metadata->fieldMappings[$propertyName]);
        }

        return $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        $this->documentManager->persist($object);
        $this->documentManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update($object)
    {
        $this->documentManager->persist($object);
        $this->documentManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->documentManager->remove($object);
        $this->documentManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function find($class, $id)
    {
        if (is_numeric($id)) {

            $value = $this->documentManager->getRepository($class)->find(intval($id));

            if (!empty($value)) {
                return $value;
            }
        }

        return $this->documentManager->getRepository($class)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy($class, array $criteria = array())
    {
        return $this->documentManager->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @return DocumentManager
     */
    public function getEntityManager()
    {
        return $this->documentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        $fieldName = $parentAssociationMapping['fieldName'];

        $metadata = $this->getMetadata($class);

        $associatingMapping = $metadata->associationMappings[$parentAssociationMapping];

        $fieldDescription = $this->getNewFieldDescriptionInstance($class, $fieldName);
        $fieldDescription->setName($parentAssociationMapping);
        $fieldDescription->setAssociationMapping($associatingMapping);

        return $fieldDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($class, $alias = 'o')
    {
        $repository = $this->getEntityManager()->getRepository($class);

        return new ProxyQuery($repository->createQueryBuilder());
    }

    /**
     * {@inheritdoc}
     */
    public function executeQuery($query)
    {
        if ($query instanceof Builder) {
            return $query->getQuery()->execute();
        }

        return $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getModelIdentifier($class)
    {
        return $this->getMetadata($class)->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierValues($document)
    {
        return array($this->documentManager->getUnitOfWork()->getDocumentIdentifier($document));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldNames($class)
    {
        return array($this->getMetadata($class)->getIdentifier());
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizedIdentifier($document)
    {
        if (is_scalar($document)) {
            throw new \RunTimeException('Invalid argument, object or null required');
        }

        // the entities is not managed
        if (!$document || !$this->getEntityManager()->getUnitOfWork()->isInIdentityMap($document)) {
            return null;
        }

        $values = $this->getIdentifierValues($document);

        return implode('-', $values);
    }

    /**
     * {@inheritDoc}
     */
    public function getUrlsafeIdentifier($entity)
    {
        return $this->getNormalizedIdentifier($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function addIdentifiersToQuery($class, ProxyQueryInterface $queryProxy, array $idx)
    {
        $queryBuilder = $queryProxy->getQueryBuilder();
        $queryBuilder->field('_id')->in($idx);
    }

    /**
     * {@inheritdoc}
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        $queryBuilder = $queryProxy->getQueryBuilder()->remove()->getQuery()->execute();

        $this->documentManager->flush();
        $this->documentManager->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
    {
        $datagrid->buildPager();
        $query = $datagrid->getQuery();

        $query->setFirstResult($firstResult);
        $query->setMaxResults($maxResult);

        return new DoctrineODMQuerySourceIterator($query instanceof ProxyQuery ? $query->getQuery() : $query, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields($class)
    {
        $metadata = $this->getEntityManager($class)->getClassMetadata($class);

        return $metadata->getFieldNames();
    }

    /**
     * {@inheritdoc}
     */
    public function getModelInstance($class)
    {
        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        $values = $datagrid->getValues();

        if ($fieldDescription->getOption('sortable') == $values['_sort_by']->getName()) {
            if ($values['_sort_order'] == 'ASC') {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }
            $values['_sort_by']    = $fieldDescription->getName();
        } else {
            $values['_sort_order'] = 'ASC';
            $values['_sort_by'] = $fieldDescription->getOption('sortable');
        }

        return array('filter' => $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        $values = $datagrid->getValues();

        $values['_page'] = $page;

        return array('filter' => $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSortValues($class)
    {
        return array(
            '_sort_order' => 'ASC',
            '_sort_by' => $this->getModelIdentifier($class),
            '_page' => 1
        );
    }

    /**
     * {@inheritdoc}
     */
    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function modelReverseTransform($class, array $array = array())
    {
        $instance = $this->getModelInstance($class);
        $metadata = $this->getMetadata($class);

        $reflClass = $metadata->reflClass;
        foreach ($array as $name => $value) {

            $reflection_property = false;
            // property or association ?
            if (array_key_exists($name, $metadata->fieldMappings)) {

                $property = $metadata->fieldMappings[$name]['fieldName'];
                $reflection_property = $metadata->reflFields[$name];
            } elseif (array_key_exists($name, $metadata->associationMappings)) {
                $property = $metadata->associationMappings[$name]['fieldName'];
            } else {
                $property = $name;
            }

            $setter = 'set' . $this->camelize($name);

            if ($reflClass->hasMethod($setter)) {
                if (!$reflClass->getMethod($setter)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflClass->getName()));
                }

                $instance->$setter($value);
            } elseif ($reflClass->hasMethod('__set')) {
                // needed to support magic method __set
                $instance->$property = $value;
            } elseif ($reflClass->hasProperty($property)) {
                if (!$reflClass->getProperty($property)->isPublic()) {
                    throw new PropertyAccessDeniedException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "set%s()"?', $property, $reflClass->getName(), ucfirst($property)));
                }

                $instance->$property = $value;
            } elseif ($reflection_property) {
                $reflection_property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * method taken from PropertyPath
     *
     * @param string $property
     *
     * @return mixed
     */
    protected function camelize($property)
    {
        return preg_replace(array('/(^|_)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $property);
    }

    /**
     * {@inheritdoc}
     */
    public function getModelCollectionInstance($class)
    {
        return new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function collectionHasElement(&$collection, &$element)
    {
        return $collection->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }
}
