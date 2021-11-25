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
        $this->client->request(Request::METHOD_GET, '/admin/tests/app/author/list');

        $this->client->clickLink('Filters');
        $this->client->clickLink('Address Street');
        $this->client->clickLink('Phone Numbers Number');

        $this->client->submitForm('Filter', [
            'filter[address__street][value]' => 'Mancha',
            'filter[phoneNumbers__number][value]' => '666-666-666',
        ]);

        self::assertSelectorTextContains('.sonata-link-identifier', 'Miguel de Cervantes');
    }

    public function testCreateDocumentWithEmbedded(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/tests/app/author/create');

        $attributeId = $crawler->filter('.author_id')->attr('name');
        static::assertIsString($attributeId);
        $attributeName = $crawler->filter('.author_name')->attr('name');
        static::assertIsString($attributeName);
        $attributeAddressStreet = $crawler->filter('.address_street')->attr('name');
        static::assertIsString($attributeAddressStreet);

        $form = $crawler->selectButton('Create and return to list')->form();
        $form[$attributeId] = 'author_new_id';
        $form[$attributeName] = 'A wonderful author';
        $form[$attributeAddressStreet] = 'A wonderful street to live';

        $crawler->filter('.field-container .sonata-ba-action[title="Add new"]')->click();
        // $crawler = $this->client->waitFor('.phone_number_number');

        $attributePhoneNumber = $crawler->filter('.phone_number_number')->attr('name');
        static::assertIsString($attributePhoneNumber);

        $form[$attributePhoneNumber] = '333-333-333';

        $this->client->submit($form);

        self::assertSelectorTextContains('.alert-success', '"A wonderful author" has been successfully created.');
    }
}
