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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Guesser;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Guesser\FilterTypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;

class FilterTypeGuesserTest extends TestCase
{
    private $guesser;
    private $modelManager;
    private $metadata;

    protected function setUp(): void
    {
        $this->guesser = new FilterTypeGuesser();
        $this->modelManager = $this->prophesize(ModelManager::class);
        $this->metadata = $this->prophesize(ClassMetadata::class);
    }

    public function testThrowsOnMissingField(): void
    {
        $this->expectException(\Sonata\DoctrineMongoDBAdminBundle\Model\MissingPropertyMetadataException::class);

        $class = 'My\Model';
        $property = 'whatever';

        $this->metadata->hasAssociation($property)->willReturn(false);

        $this->modelManager->getParentMetadataForProperty($class, $property)->willReturn([
            $this->metadata->reveal(),
            $property,
            'parent mappings, no idea what it looks like',
        ]);
        $this->guesser->guessType($class, $property, $this->modelManager->reveal());
    }
}
