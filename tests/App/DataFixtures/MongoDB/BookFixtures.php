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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\App\DataFixtures\MongoDB;

use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Author;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Book;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Category;

final class BookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $author = $this->getReference(AuthorFixtures::AUTHOR);

        \assert($author instanceof Author);

        $book = new Book('book_id', 'Don Quixote', $author);

        $category = $this->getReference(CategoryFixtures::CATEGORY);

        \assert($category instanceof Category);

        $book->addCategory($category);

        $manager->persist($book);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            AuthorFixtures::class,
        ];
    }
}
