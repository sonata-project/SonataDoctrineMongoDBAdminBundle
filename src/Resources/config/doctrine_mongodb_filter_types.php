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

use Sonata\DoctrineMongoDBAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\IdFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sonata.admin.odm.filter.type.boolean.class', BooleanFilter::class)

        ->set('sonata.admin.odm.filter.type.callback.class', CallbackFilter::class)

        ->set('sonata.admin.odm.filter.type.choice.class', ChoiceFilter::class)

        ->set('sonata.admin.odm.filter.type.model.class', ModelFilter::class)

        ->set('sonata.admin.odm.filter.type.string.class', StringFilter::class)

        ->set('sonata.admin.odm.filter.type.number.class', NumberFilter::class)

        ->set('sonata.admin.odm.filter.type.date.class', DateFilter::class)

        ->set('sonata.admin.odm.filter.type.datetime.class', DateTimeFilter::class);

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.admin.odm.filter.type.boolean', '%sonata.admin.odm.filter.type.boolean.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_boolean',
            ])

        ->set('sonata.admin.odm.filter.type.callback', '%sonata.admin.odm.filter.type.callback.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_callback',
            ])

        ->set('sonata.admin.odm.filter.type.choice', '%sonata.admin.odm.filter.type.choice.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_choice',
            ])

        ->set('sonata.admin.odm.filter.type.id', IdFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.model', '%sonata.admin.odm.filter.type.model.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_model',
            ])

        ->set('sonata.admin.odm.filter.type.string', '%sonata.admin.odm.filter.type.string.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_string',
            ])

        ->set('sonata.admin.odm.filter.type.number', '%sonata.admin.odm.filter.type.number.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_number',
            ])

        ->set('sonata.admin.odm.filter.type.date', '%sonata.admin.odm.filter.type.date.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_date',
            ])

        ->set('sonata.admin.odm.filter.type.datetime', '%sonata.admin.odm.filter.type.datetime.class%')
            ->tag('sonata.admin.filter.type', [
                'alias' => 'doctrine_mongo_datetime',
            ]);
};
