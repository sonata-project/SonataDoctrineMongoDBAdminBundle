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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\AbstractDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\AssociatedDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\ContainerDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\EmbeddedDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\ProtectedDocument;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\SimpleDocumentWithPrivateSetter;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\TestDocument;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ModelManagerTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var Stub&ManagerRegistry
     */
    private $registry;

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
            ->with(DocumentWithReferences::class)
            ->willReturn($this->getMetadataForDocumentWithAnnotations($documentWithReferencesClass));

        $this->assertSame(['id'], $modelManager->getIdentifierFieldNames($documentWithReferencesClass));
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

        $this->assertSame(42, $testDocument->getSchmeckles());
        $this->assertSame('hello', $testDocument->getMultiWordProperty());
        $this->assertTrue($testDocument->schwifty);
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

    public function testGetModelInstanceException(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectException(\InvalidArgumentException::class);

        $model->getModelInstance(AbstractDocument::class);
    }

    public function testGetModelInstanceForProtectedDocument(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->assertInstanceOf(ProtectedDocument::class, $model->getModelInstance(ProtectedDocument::class));
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
            ->expects(self::once())
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
     * @dataProvider supportsQueryDataProvider
     */
    public function testSupportsQuery(bool $expected, object $object): void
    {
        $modelManager = new ModelManager($this->registry, $this->propertyAccessor);

        $this->assertSame($expected, $modelManager->supportsQuery($object));
    }

    /**
     * @phpstan-return iterable<array{bool, object}>
     */
    public function supportsQueryDataProvider(): iterable
    {
        yield [true, new ProxyQuery($this->createStub(Builder::class))];
        yield [true, $this->createStub(Builder::class)];
        yield [false, new \stdClass()];
    }

    private function createModelManagerForClass(string $class): ModelManager
    {
        $modelManager = $this->createMock(ObjectManager::class);
        $registry = $this->createMock(ManagerRegistry::class);

        $classMetadata = $this->getMetadataForDocumentWithAnnotations($class);

        $modelManager->expects($this->once())
            ->method('getClassMetadata')
            ->with($class)
            ->willReturn($classMetadata);
        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($class)
            ->willReturn($modelManager);

        return new ModelManager($registry, $this->propertyAccessor);
    }

    private function getMetadataForDocumentWithAnnotations(string $class): ClassMetadata
    {
        $classMetadata = new ClassMetadata($class);
        $reader = new AnnotationReader();

        $annotationDriver = new AnnotationDriver($reader);
        $annotationDriver->loadMetadataForClass($class, $classMetadata);

        return $classMetadata;
    }
}
