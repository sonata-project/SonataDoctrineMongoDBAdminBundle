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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Builder;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Builder\DatagridBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class DatagridBuilderTest extends AbstractBuilderTestCase
{
    /**
     * @var DatagridBuilder
     */
    private $datagridBuilder;

    /**
     * @var MockObject&TypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * @var MockObject&FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var MockObject&FilterFactoryInterface
     */
    private $filterFactory;

    /**
     * @var MockObject&AdminInterface
     */
    private $admin;

    /**
     * @var MockObject&ModelManager
     */
    private $modelManager;

    protected function setUp(): void
    {
        $this->formFactory = $this->createStub(FormFactoryInterface::class);
        $this->filterFactory = $this->createMock(FilterFactoryInterface::class);
        $this->typeGuesser = $this->createStub(TypeGuesserInterface::class);

        $this->datagridBuilder = new DatagridBuilder(
            $this->formFactory,
            $this->filterFactory,
            $this->typeGuesser
        );

        $this->admin = $this->createMock(AdminInterface::class);
        $this->modelManager = $this->createMock(ModelManager::class);

        $this->admin
            ->method('getClass')
            ->willReturn('FakeClass');
        $this->admin
            ->method('getModelManager')
            ->willReturn($this->modelManager);
    }

    public function testGetBaseDatagrid(): void
    {
        $proxyQuery = $this->createStub(ProxyQueryInterface::class);
        $fieldDescription = $this->createStub(FieldDescriptionCollection::class);
        $formBuilder = $this->createStub(FormBuilderInterface::class);

        $this->admin->method('createQuery')->willReturn($proxyQuery);
        $this->admin->method('getList')->willReturn($fieldDescription);

        $this->modelManager->method('getIdentifierFieldNames')->willReturn(['id']);

        $this->formFactory->method('createNamedBuilder')->willReturn($formBuilder);

        $this->assertInstanceOf(
            Datagrid::class,
            $datagrid = $this->datagridBuilder->getBaseDatagrid($this->admin)
        );
        $this->assertInstanceOf(Pager::class, $datagrid->getPager());
    }

    public function testFixFieldDescription(): void
    {
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $fieldDescription = new FieldDescription('name');
        $fieldDescription->setFieldMapping($classMetadata->fieldMappings['name']);

        $this->datagridBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($classMetadata->fieldMappings['name'], $fieldDescription->getOption('field_mapping'));
        $this->assertTrue($fieldDescription->getOption('global_search'));
    }

    public function testFixFieldDescriptionWithAssociationMapping(): void
    {
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $fieldDescription = new FieldDescription('associatedDocument');
        $fieldDescription->setMappingType(ClassMetadata::ONE);
        $fieldDescription->setFieldMapping($classMetadata->fieldMappings['associatedDocument']);
        $fieldDescription->setAssociationMapping($classMetadata->associationMappings['associatedDocument']);

        $this->admin
            ->expects($this->once())
            ->method('attachAdminClass');

        $this->datagridBuilder->fixFieldDescription($this->admin, $fieldDescription);

        $this->assertSame($classMetadata->associationMappings['associatedDocument'], $fieldDescription->getOption('association_mapping'));
    }

    public function testAddFilterNoType(): void
    {
        $this->admin
            ->expects($this->once())
            ->method('addFilterFieldDescription');

        $datagrid = $this->createMock(DatagridInterface::class);
        $guessType = new TypeGuess(ModelFilter::class, [
            'guess_option' => 'guess_value',
            'guess_array_option' => [
                'guess_array_value',
            ],
        ], Guess::VERY_HIGH_CONFIDENCE);

        $fieldDescription = new FieldDescription('test');

        $this->typeGuesser->method('guessType')->willReturn($guessType);

        $this->admin->method('getCode')->willReturn('someFakeCode');

        $this->filterFactory->method('create')->willReturn(new ModelFilter());

        $this->admin->method('getLabelTranslatorStrategy')->willReturn(new FormLabelTranslatorStrategy());

        $datagrid
            ->expects($this->once())
            ->method('addFilter')
            ->with($this->isInstanceOf(ModelFilter::class));

        $this->filterFactory
            ->expects($this->once())
            ->method('create')
            ->with('test', ModelFilter::class);

        $this->datagridBuilder->addFilter(
            $datagrid,
            null,
            $fieldDescription,
            $this->admin
        );

        $this->assertSame('guess_value', $fieldDescription->getOption('guess_option'));
        $this->assertSame(['guess_array_value'], $fieldDescription->getOption('guess_array_option'));
    }

    public function testAddFilterWithType(): void
    {
        $this->admin
            ->expects($this->once())
            ->method('addFilterFieldDescription');

        $datagrid = $this->createMock(DatagridInterface::class);

        $fieldDescription = new FieldDescription('test');

        $this->filterFactory->method('create')->willReturn(new ModelFilter());

        $this->admin->method('getLabelTranslatorStrategy')->willReturn(new FormLabelTranslatorStrategy());

        $datagrid
            ->expects($this->once())
            ->method('addFilter')
            ->with($this->isInstanceOf(ModelFilter::class));

        $this->datagridBuilder->addFilter(
            $datagrid,
            ModelFilter::class,
            $fieldDescription,
            $this->admin
        );

        $this->assertSame(ModelFilter::class, $fieldDescription->getType());
    }
}
