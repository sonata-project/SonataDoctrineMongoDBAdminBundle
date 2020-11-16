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

namespace Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class AddGuesserCompilerPass implements CompilerPassInterface
{
    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $this->addGuessersToBuilder(
            $container,
            'sonata.admin.guesser.doctrine_mongodb_list_chain',
            'sonata.admin.guesser.doctrine_mongodb_list'
        );

        $this->addGuessersToBuilder(
            $container,
            'sonata.admin.guesser.doctrine_mongodb_datagrid_chain',
            'sonata.admin.guesser.doctrine_mongodb_datagrid'
        );

        $this->addGuessersToBuilder(
            $container,
            'sonata.admin.guesser.doctrine_mongodb_show_chain',
            'sonata.admin.guesser.doctrine_mongodb_show'
        );
    }

    private function addGuessersToBuilder(ContainerBuilder $container, string $builderDefinitionId, string $guessersTag): void
    {
        if (!$container->hasDefinition($builderDefinitionId)) {
            return;
        }

        $definition = $container->getDefinition($builderDefinitionId);
        $services = [];
        foreach ($container->findTaggedServiceIds($guessersTag) as $id => $attributes) {
            $services[] = new Reference($id);
        }

        $definition->replaceArgument(0, $services);
    }
}
