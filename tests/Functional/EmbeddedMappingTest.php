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

use Symfony\Component\HttpFoundation\Request;

final class EmbeddedMappingTest extends BasePantherTestCase
{
    public function testFilterByEmbedded(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $client->request(Request::METHOD_GET, '/admin/tests/app/author/list');

        $client->clickLink('Filters');
        $client->clickLink('Address Street');
        $client->clickLink('Phone Numbers Number');

        $client->submitForm('Filter', [
            'filter[address__street][value]' => 'Mancha',
            'filter[phoneNumbers__number][value]' => '666-666-666',
        ]);

        self::assertSelectorTextContains('.sonata-link-identifier', 'Miguel de Cervantes');
    }

    public function testCreateDocumentWithEmbedded(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createFirefoxClient();

        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/author/create');

        $attributeId = $crawler->filter('.author_id')->attr('name');
        $attributeName = $crawler->filter('.author_name')->attr('name');
        $attributeAddressStreet = $crawler->filter('.address_street')->attr('name');

        $form = $crawler->selectButton('Create and return to list')->form();
        $form[$attributeId] = 'author_new_id';
        $form[$attributeName] = 'A wonderful author';
        $form[$attributeAddressStreet] = 'A wonderful street to live';

        $crawler->filter('.field-container .sonata-ba-action[title="Add new"]')->click();
        $crawler = $client->waitFor('.phone_number_number');

        $attributePhoneNumber = $crawler->filter('.phone_number_number')->attr('name');

        $form[$attributePhoneNumber] = '333-333-333';

        $client->submit($form);

        self::assertSelectorTextContains('.alert-success', '"A wonderful author" has been successfully created.');
    }
}
