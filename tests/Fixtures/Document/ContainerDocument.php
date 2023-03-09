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

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class ContainerDocument
{
    /**
     * @ODM\EmbedOne(targetDocument=EmbeddedDocument::class)
     *
     * @var EmbeddedDocument
     */
    public $embeddedDocument;

    /**
     * @ODM\Field(type="int")
     *
     * @var int
     */
    protected $plainField = 0;

    public function __construct(/**
     * @ODM\ReferenceOne(targetDocument=AssociatedDocument::class)
     */
    private AssociatedDocument $associatedDocument,
        EmbeddedDocument $embeddedDocument
    ) {
        $this->embeddedDocument = $embeddedDocument;
    }

    public function getAssociatedDocument(): AssociatedDocument
    {
        return $this->associatedDocument;
    }
}
