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

/** @ODM\Document */
class DocumentWithReferences
{
    /**
     * @ODM\Id
     *
     * @var string|null
     */
    public $id;

    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    public $name;

    /**
     * @ODM\EmbedOne()
     *
     * @var EmbeddedDocument|null
     */
    public $embeddedDocument;

    /**
     * @ODM\EmbedMany()
     *
     * @var Collection<array-key, EmbeddedDocument>
     */
    public $embeddedDocuments;

    /**
     * @ODM\ReferenceOne(targetDocument=TestDocument::class)
     *
     * @var TestDocument|null
     */
    public $referenceOne;

    public function __construct(string $name, ?EmbeddedDocument $embeddedDocument = null)
    {
        $this->name = $name;
        $this->embeddedDocument = $embeddedDocument;
        $this->embeddedDocuments = new ArrayCollection();
    }
}
