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
class SimpleDocumentWithPrivateSetter
{
    /**
     * @ODM\Field(type="int")
     */
    private $schmeckles;

    public function __construct(int $schmeckles)
    {
        $this->setSchmeckles($schmeckles);
    }

    public function getSchmeckles(): int
    {
        return $this->schmeckles;
    }

    private function setSchmeckles($value): void
    {
        $this->schmeckles = $value;
    }
}
