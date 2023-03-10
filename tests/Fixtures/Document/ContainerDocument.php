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
use Doctrine\ODM\MongoDB\Types\Type;

#[ODM\Document]
class ContainerDocument
{
    #[ODM\Field(type: Type::INT)]
    protected int $plainField = 0;

    public function __construct(
        #[ODM\ReferenceOne(targetDocument: AssociatedDocument::class)]
        private AssociatedDocument $associatedDocument,
        #[ODM\EmbedOne(targetDocument: EmbeddedDocument::class)]
        public EmbeddedDocument $embeddedDocument
    ) {
    }

    public function getAssociatedDocument(): AssociatedDocument
    {
        return $this->associatedDocument;
    }
}
