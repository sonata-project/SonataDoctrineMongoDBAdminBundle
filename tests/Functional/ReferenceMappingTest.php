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

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\DomCrawler\Crawler;

final class ReferenceMappingTest extends BasePantherTestCase
{
    public function testCreateDocumentWithReferences(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/tests/app/book/create');

        $attributeId = $crawler->filter('.book_id')->attr('name');
        $attributeName = $crawler->filter('.book_name')->attr('name');

        $form = $crawler->selectButton('Create and return to list')->form();
        $form[$attributeId] = 'book_new_id';
        $form[$attributeName] = 'A wonderful book';

        $crawler->filter('.field-container .sonata-ba-action[title="Add new"]')->click();
        $crawler = $this->client->waitFor('.author_id');

        $authorForm = $this->createAuthorForm($crawler);

        $crawler = $this->client->submit($authorForm);

        $crawler->filter('.book_categories label')->each(static function (Crawler $label): void {
            $label->click();
        });

        $this->client->submit($form);

        self::assertSelectorTextContains('.alert-success', '"A wonderful book" has been successfully created.');
    }

    private function createAuthorForm(Crawler $crawler): Form
    {
        $authorAttributeId = $crawler->filter('.author_id')->attr('name');
        $authorAttributeName = $crawler->filter('.author_name')->attr('name');
        $addressAttributeName = $crawler->filter('.address_street')->attr('name');

        $authorForm = $crawler->filter('.modal-content button[name="btn_create"]')->form();
        $authorForm[$authorAttributeId] = 'new_author_id';
        $authorForm[$authorAttributeName] = 'Wonderful Author';
        $authorForm[$addressAttributeName] = 'Wonderful street';

        return $authorForm;
    }
}
