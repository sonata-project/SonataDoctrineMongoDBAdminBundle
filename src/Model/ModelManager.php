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

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as MongoDBClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ModelManager implements ModelManagerInterface
{
    public const ID_SEPARATOR = '-';

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(ManagerRegistry $registry, PropertyAccessorInterface $propertyAccessor)
    {
        $this->registry = $registry;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
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
        $nameElements = explode('.', $propertyFullName);
        $lastPropertyName = array_pop($nameElements);
        $class = $baseClass;
        $parentAssociationMappings = [];

        foreach ($nameElements as $nameElement) {
            $metadata = $this->getMetadata($class);
            $parentAssociationMappings[] = $metadata->associationMappings[$nameElement];
            $class = $metadata->getAssociationTargetClass($nameElement);
        }

        return [$this->getMetadata($class), $lastPropertyName, $parentAssociationMappings];
    }

    public function getNewFieldDescriptionInstance(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
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
            $parentAssociationMappings
        );
    }

    public function create($object): void
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->persist($object);
        $documentManager->flush();
    }

    public function update($object): void
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->persist($object);
        $documentManager->flush();
    }

    public function delete($object): void
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->remove($object);
        $documentManager->flush();
    }

    public function find(string $class, $id): ?object
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

    public function findBy(string $class, array $criteria = []): array
    {
        return $this->getDocumentManager($class)->getRepository($class)->findBy($criteria);
    }

    public function findOneBy(string $class, array $criteria = []): ?object
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

    public function createQuery(string $class, string $alias = 'o'): ProxyQueryInterface
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
            return $query->getQuery()->execute();
        }

        if ($query instanceof ProxyQuery) {
            return $query->execute();
        }

        throw new \TypeError(sprintf(
            '$query must be be an instance of "%s" or "%s"',
            Builder::class,
            ProxyQuery::class
        ));
    }

    public function getIdentifierValues(object $document): array
    {
        return [$this->getDocumentManager($document)->getUnitOfWork()->getDocumentIdentifier($document)];
    }

    public function getIdentifierFieldNames(string $class): array
    {
        return $this->getMetadata($class)->getIdentifier();
    }

    public function getNormalizedIdentifier(object $document): ?string
    {
        // the document is not managed
        if (!$this->getDocumentManager($document)->contains($document)) {
            return null;
        }

        $values = $this->getIdentifierValues($document);

        return implode(self::ID_SEPARATOR, $values);
    }

    public function getUrlSafeIdentifier(object $document): ?string
    {
        return $this->getNormalizedIdentifier($document);
    }

    public function addIdentifiersToQuery($class, ProxyQueryInterface $query, array $idx): void
    {
        $queryBuilder = $query->getQueryBuilder();
        $queryBuilder->field('_id')->in($idx);
    }

    public function batchDelete($class, ProxyQueryInterface $query): void
    {
        /** @var Query $queryBuilder */
        $queryBuilder = $query->getQuery();

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

    public function getExportFields(string $class): array
    {
        $metadata = $this->getDocumentManager($class)->getClassMetadata($class);

        return $metadata->getFieldNames();
    }

    public function getModelInstance(string $class): object
    {
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

    public function modelReverseTransform(string $class, array $array = []): object
    {
        $instance = $this->getModelInstance($class);
        $metadata = $this->getMetadata($class);

        foreach ($array as $name => $value) {
            $property = $this->getFieldName($metadata, $name);

            $this->propertyAccessor->setValue($instance, $property, $value);
        }

        return $instance;
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

    private function getMetadata(string $class): MongoDBClassMetadata
    {
        return $this->getDocumentManager($class)->getClassMetadata($class);
    }
}
