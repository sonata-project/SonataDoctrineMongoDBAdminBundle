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

use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractModelManagerTestCase extends TestCase
{
    use ClassMetadataAnnotationTrait;

    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var DocumentManager&Stub
     */
    protected $documentManager;

    protected function setUp(): void
    {
        $this->documentManager = $this->createStub(DocumentManager::class);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getManagerForClass')
            ->willReturn($this->documentManager);

        $this->modelManager = new ModelManager($managerRegistry, PropertyAccess::createPropertyAccessor());
    }
}
