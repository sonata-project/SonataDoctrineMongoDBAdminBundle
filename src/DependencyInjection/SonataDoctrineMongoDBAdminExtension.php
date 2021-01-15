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

namespace Sonata\DoctrineMongoDBAdminBundle\DependencyInjection;

use Sonata\AdminBundle\DependencyInjection\AbstractSonataAdminExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Michael Williams <michael.williams@funsational.com>
 *
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class SonataDoctrineMongoDBAdminExtension extends AbstractSonataAdminExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configs = $this->fixTemplatesConfiguration($configs, $container);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine_mongodb.php');
        $loader->load('doctrine_mongodb_filter_types.php');
        $loader->load('security.php');

        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('sonata_doctrine_mongodb_admin.templates', $config['templates']);

        // define the templates
        $container->getDefinition('sonata.admin.builder.doctrine_mongodb_list')
            ->replaceArgument(1, $config['templates']['types']['list']);

        $container->getDefinition('sonata.admin.builder.doctrine_mongodb_show')
            ->replaceArgument(1, $config['templates']['types']['show']);
    }
}
