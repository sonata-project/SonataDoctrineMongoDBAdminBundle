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

#[ODM\EmbeddedDocument]
class EmbeddedDocument
{
    #[ODM\Field(type: Type::BOOL)]
    public bool $plainField = true;

    public function __construct(
        #[ODM\Field(type: Type::INT)]
        public int $position = 0
    ) {
    }
}
