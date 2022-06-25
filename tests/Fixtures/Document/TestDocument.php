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
class TestDocument
{
    /**
     * @ODM\Field(type="bool")
     *
     * @var bool
     */
    public $schwifty = false;

    /**
     * @ODM\Field(type="int")
     *
     * @phpstan-ignore-next-line
     * This property is private on purpose, to test an error is thrown
     * when trying to reverse transform it on ModelManager.
     */
    private int $plumbus = 0;

    /**
     * @ODM\Field(type="int")
     */
    private int $schmeckles = 0;

    /**
     * @ODM\Field(type="string")
     */
    private string $multiWordProperty = '';

    public function getSchmeckles(): int
    {
        return $this->schmeckles;
    }

    public function setSchmeckles(int $value): void
    {
        $this->schmeckles = $value;
    }

    public function getMultiWordProperty(): string
    {
        return $this->multiWordProperty;
    }

    public function setMultiWordProperty(string $value): void
    {
        $this->multiWordProperty = $value;
    }
}
