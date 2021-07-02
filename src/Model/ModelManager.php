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

namespace Sonata\DoctrineMongoDBAdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as MongoDBClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
use Sonata\Exporter\Source\DoctrineODMQuerySourceIterator;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class ModelManager implements ModelManagerInterface
{
    public const ID_SEPARATOR = '-';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * NEXT_MAJOR: Make $propertyAccessor mandatory.
     */
    public function __construct(ManagerRegistry $registry, ?PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->registry = $registry;

        // NEXT_MAJOR: Remove this block.
        if (!$propertyAccessor instanceof PropertyAccessorInterface) {
            @trigger_error(sprintf(
                'Not passing an object implementing "%s" as argument 2 for "%s()" is deprecated since'
                .' sonata-project/doctrine-mongodb-admin-bundle 3.4 and will throw a %s error in 4.0.',
                PropertyAccessorInterface::class,
                __METHOD__,
                \TypeError::class
            ), \E_USER_DEPRECATED);

            $propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * NEXT_MAJOR: Change visibility to private.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be private in version 4.0
     *
     * @param string $class
     *
     * @return MongoDBClassMetadata
     */
    public function getMetadata($class)
    {
        // Remove this block.
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and'
                .' will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        return $this->getDocumentManager($class)->getClassMetadata($class);
    }

    /**
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and will be removed in version 4.0.
     *
     * Returns the model's metadata holding the fully qualified property, and the last
     * property name.
     *
     * @param string $baseClass        The base class of the model holding the fully qualified property
     * @param string $propertyFullName The name of the fully qualified property (dot ('.') separated
     *                                 property string)
     *
     * @return array
     *
     * @phpstan-return array{
     *      \Doctrine\ODM\MongoDB\Mapping\ClassMetadata,
     *      string,
     *      array
     * }
     */
    public function getParentMetadataForProperty($baseClass, $propertyFullName)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[2] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and'
                .' will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        $nameElements = explode('.', $propertyFullName);
        $lastPropertyName = array_pop($nameElements);
        $class = $baseClass;
        $parentAssociationMappings = [];

        foreach ($nameElements as $nameElement) {
            // NEXT_MAJOR: Remove 'sonata_deprecation_mute' argument.
            $metadata = $this->getMetadata($class, 'sonata_deprecation_mute');
            $parentAssociationMappings[] = $metadata->associationMappings[$nameElement];
            $class = $metadata->getAssociationTargetClass($nameElement);
        }

        // NEXT_MAJOR: Remove 'sonata_deprecation_mute' argument.
        return [$this->getMetadata($class, 'sonata_deprecation_mute'), $lastPropertyName, $parentAssociationMappings];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0
     *
     * @return bool
     */
    public function hasMetadata($class)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and'
                 .' will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        return $this->getDocumentManager($class)->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * @psalm-suppress InvalidArgument
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and will be removed in version 4.0.
     */
    public function getNewFieldDescriptionInstance($class, $name, array $options = [])
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[3] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and'
                .' will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        if (!\is_string($name)) {
            throw new \RuntimeException('The name argument must be a string');
        }

        if (!isset($options['route']['name'])) {
            $options['route']['name'] = 'edit';
        }

        if (!isset($options['route']['parameters'])) {
            $options['route']['parameters'] = [];
        }

        [$metadata, $propertyName, $parentAssociationMappings] = $this->getParentMetadataForProperty($class, $name);

        return new FieldDescription(
            $name,
            $options,
            $metadata->fieldMappings[$propertyName] ?? [],
            $metadata->associationMappings[$propertyName] ?? [],
            $parentAssociationMappings,
            $propertyName
        );
    }

    /**
     * @return void
     */
    public function create($object)
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->persist($object);
        $documentManager->flush();
    }

    /**
     * @return void
     */
    public function update($object)
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->persist($object);
        $documentManager->flush();
    }

    /**
     * @return void
     */
    public function delete($object)
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->remove($object);
        $documentManager->flush();
    }

    public function find($class, $id)
    {
        if (null === $id) {
            @trigger_error(sprintf(
                'Passing null as argument 1 for %s() is deprecated since'
                .' sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be not allowed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);

            return null;
        }

        $documentManager = $this->getDocumentManager($class);

        if (is_numeric($id)) {
            $value = $documentManager->getRepository($class)->find((int) $id);

            if (!empty($value)) {
                return $value;
            }
        }

        return $documentManager->getRepository($class)->find($id);
    }

    public function findBy($class, array $criteria = [])
    {
        return $this->getDocumentManager($class)->getRepository($class)->findBy($criteria);
    }

    public function findOneBy($class, array $criteria = [])
    {
        return $this->getDocumentManager($class)->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @param object|string $class
     *
     * @throw \RuntimeException
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager($class)
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }

        $dm = $this->registry->getManagerForClass($class);

        if (!$dm) {
            throw new \RuntimeException(sprintf('No document manager defined for class %s', $class));
        }

        return $dm;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0
     */
    public function getParentFieldDescription($parentAssociationMapping, $class)
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4'
            .' and will be removed in 4.0',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $fieldName = $parentAssociationMapping['fieldName'];

        $metadata = $this->getMetadata($class);

        $associatingMapping = $metadata->associationMappings[$parentAssociationMapping];

        $fieldDescription = $this->getNewFieldDescriptionInstance($class, $fieldName);
        $fieldDescription->setName($parentAssociationMapping);
        $fieldDescription->setAssociationMapping($associatingMapping);

        return $fieldDescription;
    }

    public function createQuery($class, $alias = 'o')
    {
        $repository = $this->getDocumentManager($class)->getRepository($class);

        \assert($repository instanceof DocumentRepository);

        return new ProxyQuery($repository->createQueryBuilder());
    }

    public function supportsQuery(object $query): bool
    {
        return $query instanceof ProxyQuery || $query instanceof Builder;
    }

    public function executeQuery($query)
    {
        if ($query instanceof Builder) {
            $result = $query->getQuery()->execute();
            \assert($result instanceof Iterator);

            return $result;
        }

        if ($query instanceof ProxyQuery) {
            return $query->execute();
        }

        // NEXT_MAJOR: Remove this trigger_error and uncomment the exception.
        @trigger_error(sprintf(
            'Passing other type than "%s" or %s as argument 1 for "%s()" is deprecated since'
            .' sonata-project/doctrine-mongodb-admin-bundle 3.5 and will throw an exception in 4.0.',
            Builder::class,
            ProxyQuery::class,
            __METHOD__
        ), \E_USER_DEPRECATED);

        //throw new \TypeError(sprintf(
        //    '$query must be be an instance of "%s" or "%s"',
        //    Builder::class,
        //    ProxyQuery::class
        //));

        // NEXT_MAJOR: Remove this line.
        return $query->execute();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.
     *
     * @psalm-suppress NullableReturnStatement
     */
    public function getModelIdentifier($class)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        return $this->getMetadata($class, 'sonata_deprecation_mute')->identifier;
    }

    public function getIdentifierValues($model)
    {
        return [$this->getDocumentManager($model)->getUnitOfWork()->getDocumentIdentifier($model)];
    }

    public function getIdentifierFieldNames($class)
    {
        // NEXT_MAJOR: Remove 'sonata_deprecation_mute' argument.
        return $this->getMetadata($class, 'sonata_deprecation_mute')->getIdentifier();
    }

    public function getNormalizedIdentifier($model)
    {
        // NEXT_MAJOR: Remove the following 2 checks and declare "object" as type for argument 1.
        if (null === $model) {
            @trigger_error(sprintf(
                'Passing null as argument 1 for %s() is deprecated since'
                .' sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be not allowed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);

            return null;
        }

        if (!\is_object($model)) {
            throw new \RuntimeException('Invalid argument, object or null required');
        }

        // the document is not managed
        if (!$this->getDocumentManager($model)->contains($model)) {
            return null;
        }

        $values = $this->getIdentifierValues($model);

        return implode(self::ID_SEPARATOR, $values);
    }

    public function getUrlSafeIdentifier($model)
    {
        // NEXT_MAJOR: Remove the following check and declare "object" as type for argument 1.
        if (!\is_object($model)) {
            @trigger_error(sprintf(
                'Passing other type than object for argument 1 for %s() is deprecated since'
                .' sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be not allowed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);

            return null;
        }

        return $this->getNormalizedIdentifier($model);
    }

    /**
     * @return void
     */
    public function addIdentifiersToQuery($class, BaseProxyQueryInterface $query, array $idx)
    {
        if (!$query instanceof ProxyQueryInterface) {
            // NEXT_MAJOR: Remove this deprecation and throw the exception
            @trigger_error(sprintf(
                'Passing %s as argument 2 to %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8'
                .' and will throw a %s error in version 4.0. You MUST pass an instance of %s instead.',
                \get_class($query),
                __METHOD__,
                \TypeError::class,
                BaseProxyQueryInterface::class
            ), \E_USER_DEPRECATED);
            // throw new \TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        $queryBuilder = $query->getQueryBuilder();
        $queryBuilder->field('_id')->in($idx);
    }

    /**
     * @return void
     */
    public function batchDelete($class, BaseProxyQueryInterface $query)
    {
        if (!$query instanceof ProxyQueryInterface) {
            // NEXT_MAJOR: Remove this deprecation and throw the exception
            @trigger_error(sprintf(
                'Passing %s as argument 2 to %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8'
                .' and will throw a %s error in version 4.0. You MUST pass an instance of %s instead.',
                \get_class($query),
                __METHOD__,
                \TypeError::class,
                BaseProxyQueryInterface::class
            ), \E_USER_DEPRECATED);
            // throw new \TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        /** @var Query $queryBuilder */
        $queryBuilder = $query->getQueryBuilder()->getQuery();

        $documentManager = $this->getDocumentManager($class);

        $i = 0;
        foreach ($queryBuilder->execute() as $object) {
            $documentManager->remove($object);

            if (0 === (++$i % 20)) {
                $documentManager->flush();
                $documentManager->clear();
            }
        }

        $documentManager->flush();
        $documentManager->clear();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.
     */
    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null)
    {
        $datagrid->buildPager();
        $query = $datagrid->getQuery();

        $query->setFirstResult($firstResult);
        $query->setMaxResults($maxResult);

        return new DoctrineODMQuerySourceIterator($query instanceof ProxyQuery ? $query->getQueryBuilder()->getQuery() : $query, $fields);
    }

    public function getExportFields($class)
    {
        $metadata = $this->getDocumentManager($class)->getClassMetadata($class);

        return $metadata->getFieldNames();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and will be removed in version 4.0.
     */
    public function getModelInstance($class)
    {
        // NEXT_MAJOR: Remove this block.
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and'
                .' will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" not found', $class));
        }

        $r = new \ReflectionClass($class);
        if ($r->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Cannot initialize abstract class: %s', $class));
        }

        $constructor = $r->getConstructor();

        if (null !== $constructor && (!$constructor->isPublic() || $constructor->getNumberOfRequiredParameters() > 0)) {
            return $r->newInstanceWithoutConstructor();
        }

        return new $class();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $values = $datagrid->getValues();

        if ($this->isFieldAlreadySorted($fieldDescription, $datagrid)) {
            if ('ASC' === $values['_sort_order']) {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }
        } else {
            $values['_sort_order'] = 'ASC';
        }

        $values['_sort_by'] = \is_string($fieldDescription->getOption('sortable')) ? $fieldDescription->getOption('sortable') : $fieldDescription->getName();

        return ['filter' => $values];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $values = $datagrid->getValues();

        if (isset($values['_sort_by']) && $values['_sort_by'] instanceof FieldDescriptionInterface) {
            $values['_sort_by'] = $values['_sort_by']->getName();
        }
        $values['_page'] = $page;

        return ['filter' => $values];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.
     */
    public function getDefaultSortValues($class)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        return [
            '_sort_order' => 'ASC',
            '_sort_by' => $this->getModelIdentifier($class, 'sonata_deprecation_mute'),
            '_page' => 1,
            '_per_page' => 25,
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.
     */
    public function getDefaultPerPageOptions(string $class): array
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return [10, 25, 50, 100, 250];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.
     */
    public function modelTransform($class, $instance)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $instance;
    }

    public function reverseTransform(object $object, array $array = []): void
    {
        // NEXT_MAJOR: Remove 'sonata_deprecation_mute' argument.
        $metadata = $this->getMetadata(\get_class($object), 'sonata_deprecation_mute');

        foreach ($array as $name => $value) {
            $property = $this->getFieldName($metadata, $name);

            $this->propertyAccessor->setValue($object, $property, $value);
        }
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8, use reverseTransform() instead.
     */
    public function modelReverseTransform($class, array $array = [])
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and will be removed in version 4.0.'
            .' Use "reverseTransform()" instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $instance = $this->getModelInstance($class);
        // NEXT_MAJOR: Remove 'sonata_deprecation_mute' argument.
        $metadata = $this->getMetadata($class, 'sonata_deprecation_mute');

        foreach ($array as $name => $value) {
            $property = $this->getFieldName($metadata, $name);

            $this->propertyAccessor->setValue($instance, $property, $value);
        }

        return $instance;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.
     */
    public function getModelCollectionInstance($class)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return new ArrayCollection();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.
     */
    public function collectionClear(&$collection)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $collection->clear();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.
     */
    public function collectionHasElement(&$collection, &$element)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $collection->contains($element);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.
     */
    public function collectionAddElement(&$collection, &$element)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $collection->add($element);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        @trigger_error(sprintf(
            'Method %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $collection->removeElement($element);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
     *
     * @param string $property
     *
     * @return mixed
     */
    protected function camelize($property)
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
    }

    private function getFieldName(MongoDBClassMetadata $metadata, string $name): string
    {
        if (\array_key_exists($name, $metadata->fieldMappings)) {
            return $metadata->fieldMappings[$name]['fieldName'];
        }

        if (\array_key_exists($name, $metadata->associationMappings)) {
            return $metadata->associationMappings[$name]['fieldName'];
        }

        return $name;
    }

    private function isFieldAlreadySorted(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid): bool
    {
        $values = $datagrid->getValues();

        if (!isset($values['_sort_by']) || !$values['_sort_by'] instanceof FieldDescriptionInterface) {
            return false;
        }

        return $values['_sort_by']->getName() === $fieldDescription->getName()
            || $values['_sort_by']->getName() === $fieldDescription->getOption('sortable');
    }
}
