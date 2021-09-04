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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Exporter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Exporter\DataSource;

final class DataSourceTest extends TestCase
{
    /**
     * @var DataSource
     */
    private $dataSource;

    protected function setUp(): void
    {
        $this->dataSource = new DataSource();
    }

    public function testItResetsTheQueryBeforeCreatingIterator(): void
    {
        $query = new Query(
            $this->createStub(DocumentManager::class),
            $this->createStub(ClassMetadata::class),
            $this->createStub(Collection::class),
            ['type' => Query::TYPE_FIND]
        );

        $queryBuilder = $this->createStub(Builder::class);
        $queryBuilder
            ->method('getQuery')
            ->willReturn($query);

        $proxyQuery = new ProxyQuery($queryBuilder);
        $proxyQuery->setFirstResult(10);
        $proxyQuery->setMaxResults(10);

        $this->dataSource->createIterator($proxyQuery, []);

        static::assertNull($proxyQuery->getFirstResult());
        static::assertNull($proxyQuery->getMaxResults());
    }

    public function testItThrowAnExceptionWithInvalidQuery(): void
    {
        $proxyQuery = $this->createStub(ProxyQueryInterface::class);

        $this->expectException(\LogicException::class);

        $this->dataSource->createIterator($proxyQuery, []);
    }
}
