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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;

#[ODM\Document]
class Book implements \Stringable
{
    /**
     * @var Collection<array-key, Category>
     */
    #[ODM\ReferenceMany(targetDocument: Category::class)]
    private Collection $categories;

    public function __construct(
        #[ODM\Id(type: Type::STRING, strategy: 'NONE')]
        private string $id = '',
        #[ODM\Field(type: Type::STRING)]
        private string $name = '',
        #[ODM\ReferenceOne(targetDocument: Author::class)]
        private ?Author $author = null
    ) {
        $this->categories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): void
    {
        $this->author = $author;
    }

    public function addCategory(Category $category): void
    {
        $this->categories->add($category);
    }

    public function removeCategory(Category $category): void
    {
        $this->categories->removeElement($category);
    }

    /**
     * @return Collection<array-key, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }
}
