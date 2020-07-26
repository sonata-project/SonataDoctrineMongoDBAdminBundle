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
use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Compiler\AddGuesserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class AddGuesserCompilerPassTest extends AbstractCompilerPassTestCase
{
    /** @dataProvider getBuilders */
    public function testAddsGuessers(string $builderServiceId, string $guesserTag): void
    {
        $builderService = new Definition(null, [[]]);
        $this->setDefinition($builderServiceId, $builderService);

        $builderGuesserService = new Definition();
        $builderGuesserService->addTag($guesserTag);
        $this->setDefinition('builder_guesser_id', $builderGuesserService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $builderServiceId,
            0,
            [
                new Reference('builder_guesser_id'),
            ]
        );
    }

    public function getBuilders(): array
    {
        return [
          'list_builder' => [
              'sonata.admin.guesser.doctrine_mongodb_list_chain',
              'sonata.admin.guesser.doctrine_mongodb_list',
          ],
            'datagrid_builder' => [
              'sonata.admin.guesser.doctrine_mongodb_datagrid_chain',
              'sonata.admin.guesser.doctrine_mongodb_datagrid',
          ], 'show_builder' => [
              'sonata.admin.guesser.doctrine_mongodb_show_chain',
              'sonata.admin.guesser.doctrine_mongodb_show',
          ],
        ];
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddGuesserCompilerPass());
    }
}
