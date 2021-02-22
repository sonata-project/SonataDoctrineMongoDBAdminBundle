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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractModelManagerTestCase extends TestCase
{
    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var DocumentManager&Stub
     */
    protected $documentManager;

    /**
     * @var ClassMetadataFactory&MockObject
     */
    protected $metadataFactory;

    protected function setUp(): void
    {
        $this->metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $this->documentManager = $this->createStub(DocumentManager::class);
        $this->documentManager
            ->method('getMetadataFactory')
            ->willReturn($this->metadataFactory);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getManagerForClass')
            ->willReturn($this->documentManager);

        $this->modelManager = new ModelManager($managerRegistry, PropertyAccess::createPropertyAccessor());
    }

    protected function getMetadataForDocumentWithAnnotations(string $class): ClassMetadata
    {
        $classMetadata = new ClassMetadata($class);
        $reader = new AnnotationReader();

        $annotationDriver = new AnnotationDriver($reader);
        $annotationDriver->loadMetadataForClass($class, $classMetadata);

        return $classMetadata;
    }
}
