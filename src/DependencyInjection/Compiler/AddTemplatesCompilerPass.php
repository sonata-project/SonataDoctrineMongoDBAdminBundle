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

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.x.
 */
class AddTemplatesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            if (!isset($attributes[0]['manager_type']) || 'doctrine_mongodb' !== $attributes[0]['manager_type']) {
                continue;
            }

            $definition = $container->getDefinition($id);
            $templates = $container->getParameter('sonata_doctrine_mongodb_admin.templates');

            if (!$definition->hasMethodCall('setFormTheme')) {
                $definition->addMethodCall('setFormTheme', [$templates['form']]);
            }

            if (!$definition->hasMethodCall('setFilterTheme')) {
                $definition->addMethodCall('setFilterTheme', [$templates['filter']]);
            }
        }
    }
}
