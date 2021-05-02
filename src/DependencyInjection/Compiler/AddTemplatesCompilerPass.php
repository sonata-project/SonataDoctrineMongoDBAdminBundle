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
use Symfony\Component\DependencyInjection\Definition;

/**
 * NEXT_MAJOR: Remove the "since" part of the internal annotation.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @internal since sonata-project/doctrine-mongodb-admin-bundle version 4.0
 *
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class AddTemplatesCompilerPass implements CompilerPassInterface
{
    /**
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            if (!isset($attributes[0]['manager_type']) || 'doctrine_mongodb' !== $attributes[0]['manager_type']) {
                continue;
            }

            $definition = $container->getDefinition($id);
            // NEXT_MAJOR: Remove this line.
            $templates = $container->getParameter('sonata_doctrine_mongodb_admin.templates');

            // NEXT_MAJOR: Remove this line and uncomment the following
            $this->mergeMethodCall($definition, 'setFormTheme', $templates['form']);
//          $this->mergeMethodCall($definition, 'setFormTheme', ['@SonataDoctrineMongoDBAdmin/Form/form_admin_fields.html.twig']);

            // NEXT_MAJOR: Remove this line and uncomment the following
            $this->mergeMethodCall($definition, 'setFilterTheme', $templates['filter']);
//          $this->mergeMethodCall($definition, 'setFilterTheme', ['@SonataDoctrineMongoDBAdmin/Form/filter_admin_fields.html.twig']);
        }
    }

    /**
     * @param array<mixed> $value
     */
    private function mergeMethodCall(Definition $definition, string $name, array $value): void
    {
        if (!$definition->hasMethodCall($name)) {
            $definition->addMethodCall($name, [$value]);

            return;
        }

        $methodCalls = $definition->getMethodCalls();

        foreach ($methodCalls as &$calls) {
            foreach ($calls as &$call) {
                if (\is_string($call)) {
                    if ($call !== $name) {
                        continue 2;
                    }

                    continue;
                }

                $call = [array_merge($call[0], $value)];
            }
        }

        $definition->setMethodCalls($methodCalls);
    }
}
