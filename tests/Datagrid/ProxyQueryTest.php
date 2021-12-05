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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Datagrid;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\EmbeddedDocument;

final class ProxyQueryTest extends TestCase
{
    /**
     * @var Builder&MockObject
     */
    private $queryBuilder;

    /**
     * @var DocumentManager
     */
    private $dm;

    protected function setUp(): void
    {
        $this->dm = DocumentManager::create(null, $this->createConfiguration());

        $this->queryBuilder = $this->createMock(Builder::class);
    }

    protected function tearDown(): void
    {
        $this->dm->createQueryBuilder(DocumentWithReferences::class)
            ->remove()
            ->getQuery()
            ->execute();
    }

    public function testSetLimitToZeroWhenResettingMaxResults(): void
    {
        $proxyQuery = new ProxyQuery($this->queryBuilder);

        $this->queryBuilder
            ->expects(static::once())
            ->method('limit')
            ->with(0);

        $proxyQuery->setMaxResults(null);

        static::assertNull($proxyQuery->getMaxResults());
    }

    public function testSetSkipToZeroWhenResettingFirstResult(): void
    {
        $proxyQuery = new ProxyQuery($this->queryBuilder);

        $this->queryBuilder
            ->expects(static::once())
            ->method('skip')
            ->with(0);

        $proxyQuery->setFirstResult(null);

        static::assertNull($proxyQuery->getFirstResult());
    }

    public function testSorting(): void
    {
        $proxyQuery = new ProxyQuery($this->queryBuilder);
        $proxyQuery->setSortBy([], ['fieldName' => 'name']);
        $proxyQuery->setSortOrder('ASC');

        static::assertSame(
            'name',
            $proxyQuery->getSortBy()
        );

        static::assertSame(
            'ASC',
            $proxyQuery->getSortOrder()
        );
    }

    public function testSortingWithWithEmbedded(): void
    {
        $queryBuilder = $this->dm->createQueryBuilder(DocumentWithReferences::class);

        $proxyQuery = new ProxyQuery($queryBuilder);
        $proxyQuery->setSortBy([['fieldName' => 'embeddedDocument']], ['fieldName' => 'position']);

        static::assertSame(
            'embeddedDocument.position',
            $proxyQuery->getSortBy()
        );
    }

    /**
     * NEXT_MAJOR: Remove the legacy group and the "doesNotPerformAssertions".
     *
     * @group legacy
     * @doesNotPerformAssertions
     * @dataProvider getDeprecatedParameters
     */
    public function testExecuteWithParameters(array $parameters, ?int $hydrationMode): void
    {
        $queryBuilder = $this->dm->createQueryBuilder(DocumentWithReferences::class);

        $proxyQuery = new ProxyQuery($queryBuilder);

        // NEXT_MAJOR: Uncomment this line
        //$this->expectException(\InvalidArgumentException::class);

        $proxyQuery->execute($parameters, $hydrationMode);
    }

    public function getDeprecatedParameters(): array
    {
        return [
            [['some' => 'parameter'], null],
            [[], 3],
        ];
    }

    public function testExecuteAllowsSorting(): void
    {
        $documentA = new DocumentWithReferences('A');
        $documentB = new DocumentWithReferences('B');

        $this->dm->persist($documentA);
        $this->dm->persist($documentB);
        $this->dm->flush();

        $queryBuilder = $this->dm->createQueryBuilder(DocumentWithReferences::class);
        $queryBuilder->select('name')->hydrate(false);
        $proxyQuery = new ProxyQuery($queryBuilder);
        $proxyQuery->setSortBy([], ['fieldName' => 'name']);
        $proxyQuery->setSortOrder('DESC');

        static::assertSame(['B', 'A'], $this->getNames($proxyQuery->execute()->toArray()));
    }

    public function testExecuteAllowsSortingWithEmbedded(): void
    {
        $documentA = new DocumentWithReferences('A', new EmbeddedDocument(1));
        $documentB = new DocumentWithReferences('B', new EmbeddedDocument(2));

        $this->dm->persist($documentA);
        $this->dm->persist($documentB);
        $this->dm->flush();

        $queryBuilder = $this->dm->createQueryBuilder(DocumentWithReferences::class);
        $queryBuilder
            ->select(['name'])
            ->hydrate(false);

        $proxyQuery = new ProxyQuery($queryBuilder);
        $proxyQuery->setSortBy([['fieldName' => 'embeddedDocument']], ['fieldName' => 'position']);
        $proxyQuery->setSortOrder('DESC');

        static::assertSame(['B', 'A'], $this->getNames($proxyQuery->execute()->toArray()));
    }

    /**
     * @param array<array{name: string}> $results
     *
     * @return string[]
     */
    private function getNames(array $results): array
    {
        return array_values(array_map(static function (array $result) {
            return $result['name'];
        }, $results));
    }

    private function createConfiguration(): Configuration
    {
        $config = new Configuration();

        $directory = sys_get_temp_dir().'/mongodb';

        $config->setProxyDir($directory);
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir($directory);
        $config->setHydratorNamespace('Hydrators');
        $config->setPersistentCollectionDir($directory);
        $config->setPersistentCollectionNamespace('PersistentCollections');
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver());

        return $config;
    }
}
