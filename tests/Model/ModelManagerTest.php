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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
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

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @dataProvider getWrongDocuments
     *
     * @param mixed $document
     */
    public function testNormalizedIdentifierException($document): void
    {
        $manager = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectException(\RuntimeException::class);

        $manager->getNormalizedIdentifier($document);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
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

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @group legacy
     */
    public function testGetNormalizedIdentifierNull(): void
    {
        $manager = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectDeprecation(
            'Passing null as argument 1 for Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::getNormalizedIdentifier() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be not allowed in version 4.0.'
        );

        $this->assertNull($manager->getNormalizedIdentifier(null));
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @group legacy
     */
    public function testSortParameters(): void
    {
        $manager = new ModelManager($this->registry, $this->propertyAccessor);

        $datagrid1 = $this->createStub(Datagrid::class);
        $datagrid2 = $this->createStub(Datagrid::class);

        $field1 = new FieldDescription('field1');
        $field2 = new FieldDescription('field2');
        $field3 = new FieldDescription('field3');
        $field3->setOption('sortable', 'field3sortBy');

        $datagrid1
            ->method('getValues')
            ->willReturn([
                '_sort_by' => $field1,
                '_sort_order' => 'ASC',
            ]);

        $datagrid2
            ->method('getValues')
            ->willReturn([
                '_sort_by' => $field3,
                '_sort_order' => 'ASC',
            ]);

        $this->expectDeprecation('Method Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::getSortParameters() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.');
        $parameters = $manager->getSortParameters($field1, $datagrid1);

        $this->assertSame('DESC', $parameters['filter']['_sort_order']);
        $this->assertSame('field1', $parameters['filter']['_sort_by']);

        $parameters = $manager->getSortParameters($field2, $datagrid1);

        $this->assertSame('ASC', $parameters['filter']['_sort_order']);
        $this->assertSame('field2', $parameters['filter']['_sort_by']);

        $parameters = $manager->getSortParameters($field3, $datagrid1);

        $this->assertSame('ASC', $parameters['filter']['_sort_order']);
        $this->assertSame('field3sortBy', $parameters['filter']['_sort_by']);

        $parameters = $manager->getSortParameters($field3, $datagrid2);

        $this->assertSame('DESC', $parameters['filter']['_sort_order']);
        $this->assertSame('field3sortBy', $parameters['filter']['_sort_by']);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetParentMetadataForProperty(): void
    {
        $containerDocumentClass = ContainerDocument::class;
        $associatedDocumentClass = AssociatedDocument::class;
        $embeddedDocumentClass = EmbeddedDocument::class;

        $dm = $this->createStub(DocumentManager::class);

        $modelManager = new ModelManager($this->registry, $this->propertyAccessor);

        $this->registry
            ->method('getManagerForClass')
            ->willReturn($dm);

        $containerDocumentMetadata = $this->getMetadataForDocumentWithAnnotations($containerDocumentClass);
        $associatedDocumentMetadata = $this->getMetadataForDocumentWithAnnotations($associatedDocumentClass);
        $embeddedDocumentMetadata = $this->getMetadataForDocumentWithAnnotations($embeddedDocumentClass);

        $dm
            ->method('getClassMetadata')
            ->willReturnMap(
                [
                    [$containerDocumentClass, $containerDocumentMetadata],
                    [$embeddedDocumentClass, $embeddedDocumentMetadata],
                    [$associatedDocumentClass, $associatedDocumentMetadata],
                ]
            );

        /** @var ClassMetadata $metadata */
        [$metadata, $lastPropertyName] = $modelManager
            ->getParentMetadataForProperty($containerDocumentClass, 'plainField');
        $this->assertSame($metadata->fieldMappings[$lastPropertyName]['type'], 'int');

        [$metadata, $lastPropertyName] = $modelManager
            ->getParentMetadataForProperty($containerDocumentClass, 'associatedDocument.plainField');
        $this->assertSame($metadata->fieldMappings[$lastPropertyName]['type'], 'int');

        [$metadata, $lastPropertyName] = $modelManager
            ->getParentMetadataForProperty($containerDocumentClass, 'embeddedDocument.plainField');
        $this->assertSame($metadata->fieldMappings[$lastPropertyName]['type'], 'bool');

        $this->assertSame($metadata->fieldMappings[$lastPropertyName]['type'], 'bool');
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testModelReverseTransformWithSetter(): void
    {
        $class = TestDocument::class;

        $manager = $this->createModelManagerForClass($class);
        $object = $manager->modelReverseTransform(
            $class,
            [
                'schmeckles' => 42,
                'multi_word_property' => 'hello',
                'schwifty' => true,
            ]
        );
        $this->assertInstanceOf($class, $object);
        $this->assertSame(42, $object->getSchmeckles());
        $this->assertSame('hello', $object->getMultiWordProperty());
        $this->assertTrue($object->schwifty);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testModelReverseTransformFailsWithPrivateSetter(): void
    {
        $class = SimpleDocumentWithPrivateSetter::class;
        $manager = $this->createModelManagerForClass($class);

        $this->expectException(NoSuchPropertyException::class);

        $manager->modelReverseTransform($class, ['schmeckles' => 42]);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testModelReverseTransformFailsWithPrivateProperties(): void
    {
        $class = TestDocument::class;
        $manager = $this->createModelManagerForClass($class);

        $this->expectException(NoSuchPropertyException::class);

        $manager->modelReverseTransform($class, ['plumbus' => 42]);
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

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testCollections(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectDeprecation('Method Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::getModelCollectionInstance() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.');
        $collection = $model->getModelCollectionInstance('whyDoWeEvenHaveThisParameter');
        $this->assertInstanceOf(ArrayCollection::class, $collection);

        $item1 = new \stdClass();
        $item2 = new \stdClass();
        $model->collectionAddElement($collection, $item1);
        $model->collectionAddElement($collection, $item2);

        $this->assertTrue($model->collectionHasElement($collection, $item1));

        $model->collectionRemoveElement($collection, $item1);

        $this->assertFalse($model->collectionHasElement($collection, $item1));

        $model->collectionClear($collection);

        $this->assertTrue($collection->isEmpty());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testModelTransform(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $instance = new \stdClass();

        $this->expectDeprecation(sprintf(
            'Method %s::modelTransform() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be removed in version 4.0.',
            ModelManager::class
        ));

        $result = $model->modelTransform('thisIsNotUsed', $instance);

        $this->assertSame($instance, $result);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPaginationParameters(): void
    {
        $datagrid = $this->createMock(DatagridInterface::class);
        $fieldDescription = $this->createMock(FieldDescriptionInterface::class);

        $datagrid->expects($this->once())
            ->method('getValues')
            ->willReturn(['_sort_by' => $fieldDescription]);

        $fieldDescription->expects($this->once())
            ->method('getName')
            ->willReturn($name = 'test');

        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectDeprecation('Method Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::getPaginationParameters() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.');
        $result = $model->getPaginationParameters($datagrid, $page = 5);

        $this->assertSame($page, $result['filter']['_page']);
        $this->assertSame($name, $result['filter']['_sort_by']);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetModelInstanceException(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectException(\InvalidArgumentException::class);

        $model->getModelInstance(AbstractDocument::class);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetModelInstanceForProtectedDocument(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->assertInstanceOf(ProtectedDocument::class, $model->getModelInstance(ProtectedDocument::class));
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @group legacy
     */
    public function testFindBadId(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectDeprecation(
            'Passing null as argument 1 for Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::find() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be not allowed in version 4.0.'
        );

        $this->assertNull($model->find('notImportant', null));
    }

    public function testGetUrlSafeIdentifierException(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectException(\RuntimeException::class);

        $model->getNormalizedIdentifier(new \stdClass());
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @group legacy
     */
    public function testGetUrlSafeIdentifierNull(): void
    {
        $model = new ModelManager($this->registry, $this->propertyAccessor);

        $this->expectDeprecation(
            'Passing null as argument 1 for Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::getNormalizedIdentifier() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.6 and will be not allowed in version 4.0.'
        );

        $this->assertNull($model->getNormalizedIdentifier(null));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetNewFieldDescriptionInstanceCreatesAFieldDescription(): void
    {
        $dm = $this->createStub(DocumentManager::class);

        $this->registry
            ->method('getManagerForClass')
            ->willReturn($dm);

        $containerDocumentClass = ContainerDocument::class;
        $containerDocumentMetadata = $this->getMetadataForDocumentWithAnnotations($containerDocumentClass);

        $dm
            ->method('getClassMetadata')
            ->with($containerDocumentClass)
            ->willReturn($containerDocumentMetadata);

        $modelManager = new ModelManager($this->registry, $this->propertyAccessor);

        $fieldDescription = $modelManager->getNewFieldDescriptionInstance($containerDocumentClass, 'plainField');

        $this->assertSame('edit', $fieldDescription->getOption('route')['name']);
        $this->assertSame($containerDocumentMetadata->getFieldMapping('plainField'), $fieldDescription->getFieldMapping());
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
