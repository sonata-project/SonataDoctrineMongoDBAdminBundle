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
class Author implements \Stringable
{
    #[ODM\EmbedOne(targetDocument: Address::class)]
    private ?Address $address = null;

    /**
     * @var Collection<array-key, PhoneNumber>
     */
    #[ODM\EmbedMany(targetDocument: PhoneNumber::class)]
    private Collection $phoneNumbers;

    public function __construct(
        #[ODM\Id(type: Type::STRING, strategy: 'NONE')]
        private string $id = '',
        #[ODM\Field(type: Type::STRING)]
        private string $name = ''
    ) {
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
