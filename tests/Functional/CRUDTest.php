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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Functional;

use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Category;
use Symfony\Component\HttpFoundation\Request;

final class CRUDTest extends BasePantherTestCase
{
    public function testFilter(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/list');

        $client->clickLink('Filters');
        $client->clickLink('Name');

        $client->submitForm('Filter', [
            'filter[name][value]' => 'Novel',
        ]);

        self::assertSelectorTextContains('.sonata-link-identifier', 'Novel');
    }

    public function testList(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/list');

        self::assertSelectorTextContains('.sonata-ba-list-field-text[objectid="category_novel"] .sonata-link-identifier', 'Novel');
    }

    public function testShow(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/category_novel/show');

        self::assertSelectorTextContains('.sonata-ba-view-container', 'category_novel');
    }

    public function testCreate(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/category/create');

        $attributeId = $crawler->filter('.category_id')->attr('name');
        $attributeName = $crawler->filter('.category_name')->attr('name');

        $client->submitForm('Create and return to list', [
            $attributeId => 'new id',
            $attributeName => 'new name',
        ]);

        self::assertSelectorTextContains('.alert-success', '"new name" has been successfully created.');
    }

    public function testEdit(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/category/category_novel/edit');

        $attributeName = $crawler->filter('.category_name')->attr('name');

        $client->submitForm('Update and close', [
            $attributeName => 'edited name',
        ]);

        self::assertSelectorTextContains('.alert-success', '"edited name" has been successfully updated.');
    }

    public function testDelete(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $documentManager = static::$kernel->getContainer()->get('doctrine_mongodb')->getManager();

        $categoryToRemove = new Category('category_to_remove', 'name');

        $documentManager->persist($categoryToRemove);
        $documentManager->flush();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/category_to_remove/delete');

        $client->submitForm('Yes, delete');

        self::assertSelectorNotExists('.sonata-ba-list-field-text[objectid="category_to_remove"] .sonata-link-identifier');
    }
}
