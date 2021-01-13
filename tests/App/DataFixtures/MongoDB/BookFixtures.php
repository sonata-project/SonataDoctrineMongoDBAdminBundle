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
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\Book;

final class BookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $book = new Book('book_id', 'Don Quixote', $this->getReference(AuthorFixtures::AUTHOR));
        $book->addCategory($this->getReference(CategoryFixtures::CATEGORY));

        $manager->persist($book);
        $manager->flush();
    }

    /**
     * @phpstan-return class-string[]
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            AuthorFixtures::class,
        ];
    }
}
