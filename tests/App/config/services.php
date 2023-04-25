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

use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Admin\AddressAdmin;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Admin\AuthorAdmin;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Admin\BookAdmin;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Admin\CategoryAdmin;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Admin\PhoneNumberAdmin;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Address;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Author;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Book;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Category;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\PhoneNumber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->load('Sonata\\DoctrineMongoDBAdminBundle\\Tests\\App\\DataFixtures\\', \dirname(__DIR__).'/DataFixtures')

        ->set(CategoryAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'model_class' => Category::class,
                'label' => 'Category',
            ])

        ->set(BookAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'model_class' => Book::class,
                'label' => 'Book',
            ])

        ->set(AuthorAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'model_class' => Author::class,
                'label' => 'Author',
            ])

        ->set(AddressAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'model_class' => Address::class,
                'label' => 'Address',
            ])

        ->set(PhoneNumberAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'model_class' => PhoneNumber::class,
                'label' => 'PhoneNumber',
            ]);
};
