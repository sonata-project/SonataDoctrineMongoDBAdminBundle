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

namespace Sonata\DoctrineMongoDBAdminBundle\FieldDescription;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class FieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
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
            $parentAssociationMappings,
            $propertyName
        );
    }

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType see https://github.com/doctrine/mongodb-odm/issues/2325
     *
     * @phpstan-param class-string $baseClass
     *
     * @phpstan-return array{
     *      ClassMetadata,
     *      string,
     *      mixed[]
     * }
     */
    private function getParentMetadataForProperty(string $baseClass, string $propertyFullName): array
    {
        $nameElements = explode('.', $propertyFullName);
        $lastPropertyName = array_pop($nameElements);
        $class = $baseClass;
        $parentAssociationMappings = [];

        foreach ($nameElements as $nameElement) {
            $metadata = $this->getMetadata($class);
            $parentAssociationMappings[] = $metadata->associationMappings[$nameElement];
            $class = $metadata->getAssociationTargetClass($nameElement);
            \assert(null !== $class);
        }

        return [$this->getMetadata($class), $lastPropertyName, $parentAssociationMappings];
    }

    /**
     * @param class-string $class
     */
    private function getMetadata(string $class): ClassMetadata
    {
        return $this->getDocumentManager($class)->getClassMetadata($class);
    }

    /**
     * @param class-string $class
     *
     * @throw \RuntimeException
     */
    private function getDocumentManager(string $class): DocumentManager
    {
        $dm = $this->registry->getManagerForClass($class);

        if (!$dm instanceof DocumentManager) {
            throw new \RuntimeException(sprintf('No document manager defined for class %s', $class));
        }

        return $dm;
    }
}
