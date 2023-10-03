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

use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Hydrator\HydratorFactory;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\UnitOfWork;
use MongoDB\Collection;
use MongoDB\Driver\Exception\RuntimeException;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Stub\Exception as ExceptionStub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Sonata\DoctrineMongoDBAdminBundle\Tests\ClassMetadataAnnotationTrait;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\EmbeddedDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\SimpleDocumentWithPrivateSetter;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\TestDocument;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ModelManagerTest extends TestCase
{
    use ClassMetadataAnnotationTrait;

    private PropertyAccessor $propertyAccessor;

    /**
     * @var Stub&ManagerRegistry
     */
    private Stub $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->createStub(ManagerRegistry::class);
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function testGetIdentifierFieldNames(): void
    {
        $dm = $this->createStub(DocumentManager::class);

        $modelManager = new ModelManager($this->registry, $this->propertyAccessor);

        $this->registry
            ->method('getManagerForClass')
            ->willReturn($dm);

        $documentWithReferencesClass = DocumentWithReferences::class;

        $dm
            ->method('getClassMetadata')
            ->willReturn($this->getMetadataForDocumentWithAttributes($documentWithReferencesClass));

        static::assertSame(['id'], $modelManager->getIdentifierFieldNames($documentWithReferencesClass));
    }

    public function testReverseTransformWithSetter(): void
    {
        $class = TestDocument::class;

        $manager = $this->createModelManagerForClass($class);
        $testDocument = new TestDocument();

        $manager->reverseTransform(
            $testDocument,
            [
                'schmeckles' => 42,
                'multi_word_property' => 'hello',
                'schwifty' => true,
            ]
        );

        static::assertSame(42, $testDocument->getSchmeckles());
        static::assertSame('hello', $testDocument->getMultiWordProperty());
        static::assertTrue($testDocument->schwifty);
    }

    public function testReverseTransformFailsWithPrivateSetter(): void
    {
        $class = SimpleDocumentWithPrivateSetter::class;
        $manager = $this->createModelManagerForClass($class);

        $this->expectException(NoSuchPropertyException::class);

        $manager->reverseTransform(new SimpleDocumentWithPrivateSetter(1), ['schmeckles' => 42]);
    }

    public function testReverseTransformFailsWithPrivateProperties(): void
    {
        $class = TestDocument::class;
        $manager = $this->createModelManagerForClass($class);

        $this->expectException(NoSuchPropertyException::class);

        $manager->reverseTransform(new TestDocument(), ['plumbus' => 42]);
    }

    public function testGetUrlSafeIdentifierException(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectException(\RuntimeException::class);

        $model->getNormalizedIdentifier(new \stdClass());
    }

    public function testCreateQuery(): void
    {
        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($this->createStub(Builder::class));

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->willReturn($repository);

        $this->registry
            ->method('getManagerForClass')
            ->willReturn($documentManager);

        $modelManager = new ModelManager($this->registry, $this->propertyAccessor);
        $modelManager->createQuery(TestDocument::class);
    }

    /**
     * @dataProvider provideSupportsQueryCases
     */
    public function testSupportsQuery(bool $expected, object $object): void
    {
        $modelManager = new ModelManager($this->registry, $this->propertyAccessor);

        static::assertSame($expected, $modelManager->supportsQuery($object));
    }

    public function testGetRealClassWithProxyObject(): void
    {
        $proxyClass = TestDocument::class;
        $baseClass = \stdClass::class;

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->expects(static::once())
            ->method('getName')
            ->willReturn($baseClass);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager->expects(static::once())
            ->method('getClassMetadata')
            ->with($proxyClass)
            ->willReturn($classMetadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(static::once())
            ->method('getManagerForClass')
            ->with($proxyClass)
            ->willReturn($documentManager);

        $modelManager = new ModelManager($registry, $this->propertyAccessor);

        static::assertSame($baseClass, $modelManager->getRealClass(new TestDocument()));
    }

    public function testGetRealClassWithNonProxyObject(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(static::once())
            ->method('getManagerForClass')
            ->with(\DateTime::class)
            ->willReturn(null);

        $modelManager = new ModelManager($registry, $this->propertyAccessor);

        static::assertSame(\DateTime::class, $modelManager->getRealClass(new \DateTime()));
    }

    /**
     * @phpstan-return iterable<array{bool, object}>
     */
    public function provideSupportsQueryCases(): iterable
    {
        yield [true, new ProxyQuery($this->createStub(Builder::class))];
        yield [true, $this->createStub(Builder::class)];
        yield [false, new \stdClass()];
    }

    /**
     * @return iterable<int|string, array<int, string|array<int, DocumentWithReferences|null>>>
     *
     * @phpstan-return iterable<int|string, array{0: string, 1: array<int, DocumentWithReferences>, 2: array<int, ?ExceptionStub>}>
     */
    public function provideFailingBatchDeleteCases(): iterable
    {
        yield [
            '#^Failed to delete object "Sonata\\\DoctrineMongoDBAdminBundle\\\Tests\\\Fixtures\\\Document\\\DocumentWithReferences"'
            .' \(id: [a-z0-9]{32}\) while performing batch deletion \(20 objects were successfully deleted before this error\)$#',
            array_fill(0, 21, new DocumentWithReferences('test', new EmbeddedDocument())),
            [null, static::throwException(new RuntimeException())],
        ];

        yield [
            '#^Failed to delete object "Sonata\\\DoctrineMongoDBAdminBundle\\\Tests\\\Fixtures\\\Document\\\DocumentWithReferences"'
            .' \(id: [a-z0-9]{32}\) while performing batch deletion$#',
            [new DocumentWithReferences('test', new EmbeddedDocument()), new DocumentWithReferences('test', new EmbeddedDocument())],
            [static::throwException(new RuntimeException())],
        ];

        yield [
            '#^Failed to perform batch deletion for "Sonata\\\DoctrineMongoDBAdminBundle\\\Tests\\\Fixtures\\\Document\\\DocumentWithReferences"'
            .' objects$#',
            [],
            [static::throwException(new RuntimeException())],
        ];
    }

    /**
     * @param array<int, DocumentWithReferences> $result
     * @param array<int, ExceptionStub|null>     $onConsecutiveFlush
     *
     * @dataProvider provideFailingBatchDeleteCases
     */
    public function testFailingBatchDelete(string $expectedExceptionMessage, array $result, array $onConsecutiveFlush): void
    {
        $batchSize = 20;

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->expects([] === $result ? static::never() : static::atLeastOnce())
            ->method('newInstance')
            ->willReturn(new DocumentWithReferences('test', new EmbeddedDocument()));
        $classMetadata->name = DocumentWithReferences::class;

        $dm = $this->createMock(DocumentManager::class);
        $dm
            ->expects([] === $result ? static::never() : static::atLeastOnce())
            ->method('contains')
            ->willReturnCallback(static fn (object $document): bool => $document instanceof DocumentWithReferences);

        $collection = $this->createMock(Collection::class);
        $collection
            ->expects(static::atLeastOnce())
            ->method('find')
            ->willReturn((static function () use ($result): \Traversable {
                foreach ($result as $document) {
                    yield [
                        '_id' => $document->id,
                    ];
                }
            })());

        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder
            ->expects(static::atLeastOnce())
            ->method('getQuery')
            ->willReturn(new Query(
                $dm,
                $classMetadata,
                $collection,
                [
                    'type' => Query::TYPE_FIND,
                    'query' => ['$id' => '00000000000011190000000000000000'],
                ]
            ));

        $documentRepository = $this->createMock(DocumentRepository::class);
        $documentRepository
            ->expects(static::atLeastOnce())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $dm
            ->expects(static::atLeastOnce())
            ->method('getRepository')
            ->with(DocumentWithReferences::class)
            ->willReturn($documentRepository);
        $dm->expects([] === $result ? static::never() : static::atLeastOnce())
            ->method('getClassMetadata')
            ->with(DocumentWithReferences::class)
            ->willReturn($classMetadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(static::atLeastOnce())
            ->method('getManagerForClass')
            ->with(DocumentWithReferences::class)
            ->willReturn($dm);

        $dm
            ->expects(static::exactly(\count($result)))
            ->method('remove');
        $dm
            ->expects(static::exactly([] === $result ? 1 : (int) ceil(\count($result) / $batchSize)))
            ->method('flush')
            ->will(static::onConsecutiveCalls(
                ...$onConsecutiveFlush
            ));

        $eventManager = new EventManager();
        $hydratorFactory = new HydratorFactory(
            $dm,
            $eventManager,
            sys_get_temp_dir(),
            'Sonata\DoctrineMongoDBAdminBundle\Tests\Hydrator',
            Configuration::AUTOGENERATE_FILE_NOT_EXISTS
        );
        $uow = new UnitOfWork($dm, $eventManager, $hydratorFactory);
        /** @psalm-suppress InternalMethod */
        $hydratorFactory->setUnitOfWork($uow);

        $dm
            ->expects(static::atLeastOnce())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $modelManager = new ModelManager($registry, $this->propertyAccessor);

        $proxyQuery = $modelManager->createQuery(DocumentWithReferences::class);

        $this->expectException(ModelManagerException::class);
        $this->expectExceptionMessageMatches($expectedExceptionMessage);

        $modelManager->batchDelete(DocumentWithReferences::class, $proxyQuery, $batchSize);
    }

    /**
     * @phpstan-template T of object
     * @phpstan-param class-string<T> $class
     * @phpstan-return ModelManager<T>
     */
    private function createModelManagerForClass(string $class): ModelManager
    {
        $modelManager = $this->createMock(DocumentManager::class);
        $registry = $this->createMock(ManagerRegistry::class);

        $classMetadata = $this->getMetadataForDocumentWithAttributes($class);

        $modelManager->expects(static::once())
            ->method('getClassMetadata')
            ->with($class)
            ->willReturn($classMetadata);
        $registry->expects(static::once())
            ->method('getManagerForClass')
            ->with($class)
            ->willReturn($modelManager);

        /** @phpstan-var ModelManager<T> $modelManager */
        $modelManager = new ModelManager($registry, $this->propertyAccessor);

        return $modelManager;
    }
}
