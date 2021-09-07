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

        $this->assertContainerBuilderHasService('sonata.admin.manager.doctrine_mongodb');
        $this->assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_form');
        $this->assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_list');
        $this->assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_list');
        $this->assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_list_chain');
        $this->assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_show');
        $this->assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_show');
        $this->assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_show_chain');
        $this->assertContainerBuilderHasService('sonata.admin.builder.doctrine_mongodb_datagrid');
        $this->assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_datagrid');
        $this->assertContainerBuilderHasService('sonata.admin.guesser.doctrine_mongodb_datagrid_chain');

        $this->assertContainerBuilderHasService('sonata.admin.manager.doctrine_mongodb');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.boolean');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.callback');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.choice');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.id');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.model');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.string');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.number');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.date');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.datetime');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.date_range');
        $this->assertContainerBuilderHasService('sonata.admin.odm.filter.type.datetime_range');

        $this->assertContainerBuilderHasService('sonata.admin.manipulator.acl.object.doctrine_mongodb');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
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
