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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as MongoDBClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @phpstan-template T of object
 *
 * @template-implements ModelManagerInterface<T>
 */
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

    public function create(object $object): void
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->persist($object);
        $documentManager->flush();
    }

    public function update(object $object): void
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->persist($object);
        $documentManager->flush();
    }

    public function delete(object $object): void
    {
        $documentManager = $this->getDocumentManager($object);
        $documentManager->remove($object);
        $documentManager->flush();
    }

    /**
     * @param int|string $id
     *
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function find(string $class, $id): ?object
    {
        $documentManager = $this->getDocumentManager($class);

        return $documentManager->getRepository($class)->find($id);
    }

    /**
     * @phpstan-param class-string<T> $class
     * @phpstan-return array<T>
     */
    public function findBy(string $class, array $criteria = []): array
    {
        return $this->getDocumentManager($class)->getRepository($class)->findBy($criteria);
    }

    /**
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function findOneBy(string $class, array $criteria = []): ?object
    {
        return $this->getDocumentManager($class)->getRepository($class)->findOneBy($criteria);
    }

    /**
     * NEXT_MAJOR: Change visibility to private.
     *
     * @param object|class-string<T> $class
     *
     * @throw \RuntimeException
     */
    public function getDocumentManager($class): DocumentManager
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }

        $dm = $this->registry->getManagerForClass($class);

        if (!$dm instanceof DocumentManager) {
            throw new \RuntimeException(sprintf('No document manager defined for class %s', $class));
        }

        return $dm;
    }

    public function createQuery(string $class, string $alias = 'o'): BaseProxyQueryInterface
    {
        $repository = $this->getDocumentManager($class)->getRepository($class);

        \assert($repository instanceof DocumentRepository);

        return new ProxyQuery($repository->createQueryBuilder());
    }

    public function supportsQuery(object $query): bool
    {
        return $query instanceof ProxyQuery || $query instanceof Builder;
    }

    public function executeQuery(object $query)
    {
        if ($query instanceof Builder) {
            $result = $query->getQuery()->execute();
            \assert($result instanceof Iterator);

            return $result;
        }

        if ($query instanceof ProxyQuery) {
            /** @phpstan-var Iterator<T> $results */
            $results = $query->execute();

            return $results;
        }

        throw new \TypeError(sprintf(
            '$query must be be an instance of "%s" or "%s"',
            Builder::class,
            ProxyQuery::class
        ));
    }

    public function getIdentifierValues(object $model): array
    {
        return [$this->getDocumentManager($model)->getUnitOfWork()->getDocumentIdentifier($model)];
    }

    public function getIdentifierFieldNames(string $class): array
    {
        return array_filter($this->getMetadata($class)->getIdentifier());
    }

    public function getNormalizedIdentifier(object $model): ?string
    {
        // the document is not managed
        if (!$this->getDocumentManager($model)->contains($model)) {
            return null;
        }

        $values = $this->getIdentifierValues($model);

        return implode(self::ID_SEPARATOR, $values);
    }

    public function getUrlSafeIdentifier(object $model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    public function addIdentifiersToQuery(string $class, BaseProxyQueryInterface $query, array $idx): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        $queryBuilder = $query->getQueryBuilder();
        $queryBuilder->field('_id')->in($idx);
    }

    public function batchDelete(string $class, BaseProxyQueryInterface $query): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        $queryBuilder = $query->getQueryBuilder()->getQuery();

        $documentManager = $this->getDocumentManager($class);

        $iterator = $queryBuilder->execute();

        \assert($iterator instanceof Iterator);

        $i = 0;
        foreach ($iterator as $object) {
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

    public function reverseTransform(object $object, array $array = []): void
    {
        $metadata = $this->getMetadata(\get_class($object));

        foreach ($array as $name => $value) {
            $property = $this->getFieldName($metadata, $name);

            $this->propertyAccessor->setValue($object, $property, $value);
        }
    }

    /**
     * @param MongoDBClassMetadata<T> $metadata
     */
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

    /**
     * @param class-string<T> $class
     *
     * @return MongoDBClassMetadata<T>
     */
    private function getMetadata(string $class): MongoDBClassMetadata
    {
        return $this->getDocumentManager($class)->getClassMetadata($class);
    }
}
