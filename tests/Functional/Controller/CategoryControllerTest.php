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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Functional\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\AppKernel;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Category;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

final class CategoryControllerTest extends PantherTestCase
{
    /**
     * @var DocumentManager
     */
    private static $dm;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $publicDir = __DIR__.'/../../App/public';
        $_SERVER['PANTHER_WEB_SERVER_DIR'] = $publicDir;

        $application = new Application(new AppKernel());
        $application->setAutoExit(false);

        // Install Assets
        $input = new ArrayInput(['command' => 'assets:install', 'target' => $publicDir, '--symlink' => true]);
        $application->run($input, new NullOutput());
    }

    protected function tearDown(): void
    {
        if (null === self::$dm) {
            return;
        }

        self::$dm->createQueryBuilder(Category::class)
            ->remove()
            ->getQuery()
            ->execute();
    }

    public function testFilter(): void
    {
        $client = static::createPantherClient([
            'browser' => PantherTestCase::FIREFOX,
        ]);

        $this->loadFixtures();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/list');

        $client->clickLink('Filters');
        $client->clickLink('Name');

        $client->submitForm('Filter', [
            'filter[name][value]' => 'category name',
        ]);

        self::assertSelectorTextContains('.sonata-link-identifier', 'category name');
    }

    public function testList(): void
    {
        $client = static::createPantherClient([
            'browser' => PantherTestCase::FIREFOX,
        ]);

        $this->loadFixtures();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/list');

        self::assertSelectorTextContains('.sonata-link-identifier', 'category name');
    }

    public function testShow(): void
    {
        $client = static::createPantherClient([
            'browser' => PantherTestCase::FIREFOX,
        ]);

        $this->loadFixtures();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/category_id/show');

        self::assertSelectorTextContains('.sonata-ba-view-container', 'category_id');
    }

    public function testCreate(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createPantherClient([
            'browser' => PantherTestCase::FIREFOX,
        ]);

        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/category/create');

        $attributeId = $crawler->filter('.category_id')->attr('name');
        $attributeName = $crawler->filter('.category_name')->attr('name');

        $client->submitForm('Create and return to list', [
            $attributeId => 'new id',
            $attributeName => 'new name',
        ]);

        self::assertSelectorTextContains('.sonata-link-identifier', 'new name');
    }

    public function testEdit(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createPantherClient([
            'browser' => PantherTestCase::FIREFOX,
        ]);

        $this->loadFixtures();

        $crawler = $client->request(Request::METHOD_GET, '/admin/tests/app/category/category_id/edit');

        $attributeName = $crawler->filter('.category_name')->attr('name');

        $client->submitForm('Update and close', [
            $attributeName => 'edited name',
        ]);

        self::assertSelectorTextContains('.sonata-link-identifier', 'edited name');
    }

    public function testDelete(): void
    {
        // Workaround to avoid timeout https://github.com/symfony/panther/issues/155
        self::stopWebServer();

        $client = static::createPantherClient([
            'browser' => PantherTestCase::FIREFOX,
        ]);

        $this->loadFixtures();

        $client->request(Request::METHOD_GET, '/admin/tests/app/category/category_id/delete');

        $client->submitForm('Yes, delete');

        self::assertSelectorNotExists('.sonata-link-identifier');
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    private function loadFixtures(): void
    {
        if (null === self::$dm) {
            $container = static::$kernel->getContainer();
            self::$dm = $container->get('doctrine_mongodb')->getManager();
        }

        $category = new Category('category_id', 'category name');

        self::$dm->persist($category);
        self::$dm->flush();
    }
}
