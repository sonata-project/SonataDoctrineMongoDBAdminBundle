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

/** @ODM\Document */
class Author
{
    /**
     * @ODM\Id(strategy="NONE", type="string")
     */
    private string $id;

    /**
     * @ODM\Field(type="string")
     */
    private string $name;

    /**
     * @ODM\EmbedOne(targetDocument=Address::class)
     */
    private ?Address $address = null;

    /**
     * @ODM\EmbedMany(targetDocument=PhoneNumber::class)
     *
     * @var Collection<array-key, PhoneNumber>
     */
    private Collection $phoneNumbers;

    public function __construct(string $id = '', string $name = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->phoneNumbers = new ArrayCollection();
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

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    public function addPhoneNumber(PhoneNumber $phonenumber): void
    {
        $this->phoneNumbers->add($phonenumber);
    }

    public function removePhoneNumber(PhoneNumber $phonenumber): void
    {
        $this->phoneNumbers->removeElement($phonenumber);
    }

    /**
     * @return Collection<array-key, PhoneNumber>
     */
    public function getPhoneNumbers(): Collection
    {
        return $this->phoneNumbers;
    }
}
