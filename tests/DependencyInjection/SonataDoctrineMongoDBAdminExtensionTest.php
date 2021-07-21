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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\SonataDoctrineMongoDBAdminExtension;

final class SonataDoctrineMongoDBAdminExtensionTest extends AbstractExtensionTestCase
{
    public function testEntityManagerSetFactory(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();

        self::assertContainerBuilderHasService('sonata.admin.manager.doctrine_mongodb');
        self::assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_form');
        self::assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_list');
        self::assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_list');
        self::assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_list_chain');
        self::assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_show');
        self::assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_show');
        self::assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_show_chain');
        self::assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_datagrid');
        self::assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_datagrid');
        self::assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_datagrid_chain');

        self::assertContainerBuilderHasService('sonata.admin.manager.doctrine_mongodb');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.boolean');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.callback');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.choice');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.id');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.model');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.string');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.number');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.date');
        self::assertContainerBuilderHasService('sonata.admin.odm.filter.type.datetime');

        self::assertContainerBuilderHasService('sonata.admin.manipulator.acl.object.doctrine_mongodb');
        self::assertContainerBuilderHasServiceDefinitionWithArgument(
            'sonata.admin.manipulator.acl.object.doctrine_mongodb',
            0,
            'doctrine_mongodb'
        );
    }

    protected function getContainerExtensions(): array
    {
        return [
            new SonataDoctrineMongoDBAdminExtension(),
        ];
    }
}
