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
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\IdFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\NumberFilter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.admin.odm.filter.type.boolean', BooleanFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.callback', CallbackFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.choice', ChoiceFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.id', IdFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.model', ModelFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.string', StringFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.number', NumberFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.date', DateFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.datetime', DateTimeFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.date_range', DateRangeFilter::class)
            ->tag('sonata.admin.filter.type')

        ->set('sonata.admin.odm.filter.type.datetime_range', DateTimeRangeFilter::class)
            ->tag('sonata.admin.filter.type');
};
