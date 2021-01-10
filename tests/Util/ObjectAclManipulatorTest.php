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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Util;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Security\Handler\NoopSecurityHandler;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentForAcl;
use Sonata\DoctrineMongoDBAdminBundle\Util\ObjectAclManipulator;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;

final class ObjectAclManipulatorTest extends TestCase
{
    /**
     * @var DocumentManager
     */
    private $dm;

    protected function setUp(): void
    {
        $this->dm = DocumentManager::create(null, $this->createConfiguration());
    }

    public function testFailsWithoutACLSecurityHandler(): void
    {
        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getSecurityHandler')
            ->willReturn(new NoopSecurityHandler());

        $objectAclManipulator = new ObjectAclManipulator($this->createStub(ManagerRegistry::class));

        $output = new BufferedOutput();

        $objectAclManipulator->batchConfigureAcls($output, $admin);

        self::assertStringContainsString('Admin class is not configured to use ACL', $output->fetch());
    }

    public function testBatchConfigureAcls(): void
    {
        $this->dm->persist(new DocumentForAcl());
        $this->dm->flush();

        $aclSecurityHandler = $this->createStub(AclSecurityHandlerInterface::class);
        $aclSecurityHandler
            ->method('findObjectAcls')
            ->willReturn(new \SplObjectStorage());

        $aclSecurityHandler
            ->method('buildSecurityInformation')
            ->willReturn([]);

        $aclSecurityHandler
            ->method('createAcl')
            ->willReturn($this->createStub(MutableAclInterface::class));

        $admin = $this->createStub(AdminInterface::class);
        $admin
            ->method('getSecurityHandler')
            ->willReturn($aclSecurityHandler);

        $admin
            ->method('getClass')
            ->willReturn(DocumentForAcl::class);

        $modelManager = $this->createStub(ModelManagerInterface::class);

        $admin
            ->method('getModelManager')
            ->willReturn($modelManager);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry
            ->method('getManagerForClass')
            ->willReturn($this->dm);

        $objectAclManipulator = new ObjectAclManipulator($managerRegistry);

        $output = new BufferedOutput();

        $objectAclManipulator->batchConfigureAcls($output, $admin);

        self::assertStringContainsString('[TOTAL] generated class ACEs for 1 objects (added 1, updated 0)', $output->fetch());

        $this->dm->createQueryBuilder(DocumentForAcl::class)
            ->remove()
            ->getQuery()
            ->execute();
    }

    private function createConfiguration(): Configuration
    {
        $config = new Configuration();

        $directory = sys_get_temp_dir().'/mongodb';

        $config->setProxyDir($directory);
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir($directory);
        $config->setHydratorNamespace('Hydrators');
        $config->setPersistentCollectionDir($directory);
        $config->setPersistentCollectionNamespace('PersistentCollections');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());

        return $config;
    }
}
