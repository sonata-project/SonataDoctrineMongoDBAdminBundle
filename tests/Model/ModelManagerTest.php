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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ModelManagerTest extends TestCase
{
    public function testFilterEmpty(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        new ModelManager($registry);
    }

    /**
     * @dataProvider getWrongDocuments
     *
     * @param mixed $document
     */
    public function testNormalizedIdentifierException($document): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $model = new ModelManager($registry);

        $this->expectException(\RuntimeException::class);

        $model->getNormalizedIdentifier($document);
    }

    public function getWrongDocuments(): iterable
    {
        yield [0];
        yield [1];
        yield [false];
        yield [true];
        yield [[]];
        yield [''];
        yield ['sonata-project'];
    }

    public function testGetNormalizedIdentifierNull(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $model = new ModelManager($registry);

        $this->assertNull($model->getNormalizedIdentifier(null));
    }
}
