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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->load('Sonata\\DoctrineMongoDBAdminBundle\\Tests\\App\\DataFixtures\\', dirname(__DIR__).'/DataFixtures')

        ->set(CategoryAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'label' => 'Category',
            ])
            ->args([
                '',
                Category::class,
                null,
            ])

        ->set(BookAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'label' => 'Book',
            ])
            ->args([
                '',
                Book::class,
                null,
            ])

        ->set(AuthorAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'label' => 'Author',
            ])
            ->args([
                '',
                Author::class,
                null,
            ])

        ->set(AddressAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'label' => 'Address',
            ])
            ->args([
                '',
                Address::class,
                null,
            ])

        ->set(PhoneNumberAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'label' => 'PhoneNumber',
            ])
            ->args([
                '',
                PhoneNumber::class,
                null,
            ]);
};
