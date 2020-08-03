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

/** @ODM\Document */
class SimpleAnnotationDocument
{
    /** @ODM\Id */
    private $id;

    /** @ODM\Field(type="string") */
    private $name;

    /**
     * @ODM\EmbedOne()
     */
    private $associatedDocument;

    /**
     * @ODM\EmbedMany()
     */
    private $embeddedDocument;

    /**
     * @ODM\ReferenceOne(targetDocument=SimpleDocument::class)
     */
    private $referenceOne;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
