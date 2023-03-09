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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;

#[ODM\Document]
class DocumentWithReferences
{
    #[ODM\Id]
    public ?string $id = null;

    /**
     * @var Collection<array-key, EmbeddedDocument>
     */
    #[ODM\EmbedMany]
    public Collection $embeddedDocuments;

    #[ODM\ReferenceOne(targetDocument: TestDocument::class)]
    public ?TestDocument $referenceOne = null;

    public function __construct(
        #[ODM\Field(type: Type::STRING)]
        public string $name,
        #[ODM\EmbedOne]
        public ?EmbeddedDocument $embeddedDocument = null
    ) {
        $this->embeddedDocuments = new ArrayCollection();
    }
}
