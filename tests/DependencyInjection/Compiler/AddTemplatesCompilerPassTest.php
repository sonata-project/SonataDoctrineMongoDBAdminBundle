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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Compiler\AddTemplatesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class AddTemplatesCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testSetThemes(): void
    {
        $adminServiceId = 'admin_id';
        $adminService = new Definition();
        $adminService->addTag('sonata.admin', ['manager_type' => 'doctrine_mongodb']);
        $adminService->addMethodCall('setFormTheme', [['foo.html.twig']]);
        $adminService->addMethodCall('setFilterTheme', [['bar.html.twig']]);
        $this->setDefinition($adminServiceId, $adminService);

        $adminServiceNotManagedId = 'admin_not_managed_id';
        $adminServiceNotManaged = new Definition();
        $adminServiceNotManaged->addTag('sonata.admin', ['manager_type' => 'type']);
        $this->setDefinition($adminServiceNotManagedId, $adminServiceNotManaged);

        $this->compile();

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $adminServiceId,
            'setFormTheme',
            [
                ['foo.html.twig', '@SonataDoctrineMongoDBAdmin/Form/form_admin_fields.html.twig'],
            ]
        );

        self::assertContainerBuilderHasServiceDefinitionWithMethodCall(
            $adminServiceId,
            'setFilterTheme',
            [
                ['bar.html.twig', '@SonataDoctrineMongoDBAdmin/Form/filter_admin_fields.html.twig'],
            ]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddTemplatesCompilerPass());
    }
}
