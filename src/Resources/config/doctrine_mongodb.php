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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\AdminBundle\FieldDescription\TypeGuesserChain;
use Sonata\DoctrineMongoDBAdminBundle\Builder\DatagridBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Builder\FormContractor;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ListBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Builder\ShowBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Exporter\DataSource;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescriptionFactory;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\TypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.manager.doctrine_mongodb', ModelManager::class)
            ->tag('sonata.admin.manager')
            ->args([
                service('doctrine_mongodb'),
                service('property_accessor'),
            ])

        ->set('sonata.admin.builder.doctrine_mongodb_form', FormContractor::class)
            ->args([
                service('form.factory'),
                service('form.registry'),
            ])

        ->set('sonata.admin.builder.doctrine_mongodb_list', ListBuilder::class)
            ->args([
                service('sonata.admin.guesser.doctrine_mongodb_list_chain'),
                abstract_arg('templates'),
            ])

        ->set('sonata.admin.guesser.doctrine_mongodb_list', TypeGuesser::class)
            ->tag('sonata.admin.guesser.doctrine_mongodb_list')

        ->set('sonata.admin.guesser.doctrine_mongodb_list_chain', TypeGuesserChain::class)
            ->args([
                [
                    service('sonata.admin.guesser.doctrine_mongodb_list'),
                ],
            ])

        ->set('sonata.admin.builder.doctrine_mongodb_show', ShowBuilder::class)
            ->args([
                service('sonata.admin.guesser.doctrine_mongodb_show_chain'),
                abstract_arg('templates'),
            ])

        ->set('sonata.admin.guesser.doctrine_mongodb_show', TypeGuesser::class)
            ->tag('sonata.admin.guesser.doctrine_mongodb_show')

        ->set('sonata.admin.guesser.doctrine_mongodb_show_chain', TypeGuesserChain::class)
            ->args([
                [
                    service('sonata.admin.guesser.doctrine_mongodb_list'),
                ],
            ])

        ->set('sonata.admin.builder.doctrine_mongodb_datagrid', DatagridBuilder::class)
            ->args([
                service('form.factory'),
                service('sonata.admin.builder.filter.factory'),
                service('sonata.admin.guesser.doctrine_mongodb_datagrid_chain'),
                param('form.type_extension.csrf.enabled'),
            ])

        ->set('sonata.admin.guesser.doctrine_mongodb_datagrid', FilterTypeGuesser::class)
            ->tag('sonata.admin.guesser.doctrine_mongodb_datagrid')

        ->set('sonata.admin.guesser.doctrine_mongodb_datagrid_chain', TypeGuesserChain::class)
            ->args([
                [
                    service('sonata.admin.guesser.doctrine_mongodb_datagrid'),
                ],
            ])

        ->set('sonata.admin.data_source.doctrine_mongodb', DataSource::class)

        ->set('sonata.admin.field_description_factory.doctrine_mongodb', FieldDescriptionFactory::class)
            ->args([
                service('doctrine_mongodb'),
            ]);
};
