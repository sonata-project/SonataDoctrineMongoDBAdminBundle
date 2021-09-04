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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\AdminBundle\Datagrid\SimplePager;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Sonata\AdminBundle\Translator\FormLabelTranslatorStrategy;
use Sonata\DoctrineMongoDBAdminBundle\Builder\DatagridBuilder;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Filter\ModelFilter;
use Sonata\DoctrineMongoDBAdminBundle\Tests\ClassMetadataAnnotationTrait;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class DatagridBuilderTest extends TestCase
{
    use ClassMetadataAnnotationTrait;

    /**
     * @var DatagridBuilder
     */
    private $datagridBuilder;

    /**
     * @var Stub&TypeGuesserInterface
     */
    private $typeGuesser;

    /**
     * @var Stub&FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var MockObject&FilterFactoryInterface
     */
    private $filterFactory;

    /**
     * @var MockObject&AdminInterface<object>
     */
    private $admin;

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
    }

    /**
     * @phpstan-param class-string $pager
     *
     * @dataProvider getBaseDatagridData
     */
    public function testGetBaseDatagrid(string $pagerType, string $pager): void
    {
        $proxyQuery = $this->createStub(ProxyQueryInterface::class);
        $fieldDescription = new FieldDescriptionCollection();
        $formBuilder = $this->createStub(FormBuilderInterface::class);

        $this->admin->method('getPagerType')->willReturn($pagerType);
        $this->admin->method('createQuery')->willReturn($proxyQuery);
        $this->admin->method('getList')->willReturn($fieldDescription);

        $this->formFactory->method('createNamedBuilder')->willReturn($formBuilder);

        $datagrid = $this->datagridBuilder->getBaseDatagrid($this->admin);
        static::assertInstanceOf(Datagrid::class, $datagrid);
        static::assertInstanceOf($pager, $datagrid->getPager());
    }

    /**
     * @phpstan-return iterable<array-key, array{string, class-string}>
     */
    public function getBaseDatagridData(): iterable
    {
        return [
            'simple' => [
                Pager::TYPE_SIMPLE,
                SimplePager::class,
            ],
            'default' => [
                Pager::TYPE_DEFAULT,
                Pager::class,
            ],
        ];
    }

    public function testFixFieldDescription(): void
    {
        $documentClass = DocumentWithReferences::class;
        $classMetadata = $this->getMetadataForDocumentWithAnnotations($documentClass);

        $fieldDescription = new FieldDescription('name', [], $classMetadata->fieldMappings['name']);
        $fieldDescription->setAdmin($this->admin);

        $this->admin
            ->method('getClass')
            ->willReturn($documentClass);

        $this->datagridBuilder->fixFieldDescription($fieldDescription);

        static::assertSame($classMetadata->fieldMappings['name'], $fieldDescription->getOption('field_mapping'));
    }

    public function testFixFieldDescriptionWithAssociationMapping(): void
    {
        $documentClass = DocumentWithReferences::class;
        $classMetadata = $this->getMetadataForDocumentWithAnnotations($documentClass);

        $fieldDescription = new FieldDescription(
            'embeddedDocument',
            [],
            $classMetadata->fieldMappings['embeddedDocument'],
            $classMetadata->associationMappings['embeddedDocument']
        );
        $fieldDescription->setAdmin($this->admin);

        $this->admin
            ->expects(static::once())
            ->method('attachAdminClass');

        $this->datagridBuilder->fixFieldDescription($fieldDescription);

        static::assertSame($classMetadata->associationMappings['embeddedDocument'], $fieldDescription->getOption('association_mapping'));
    }

    public function testAddFilterNoType(): void
    {
        $this->admin
            ->expects(static::once())
            ->method('addFilterFieldDescription');

        $datagrid = $this->createMock(DatagridInterface::class);
        $guessType = new TypeGuess(ModelFilter::class, [
            'guess_option' => 'guess_value',
            'guess_array_option' => [
                'guess_array_value',
            ],
        ], Guess::VERY_HIGH_CONFIDENCE);

        $fieldDescription = new FieldDescription('test');
        $fieldDescription->setAdmin($this->admin);

        $this->typeGuesser->method('guess')->willReturn($guessType);

        $this->admin->method('getCode')->willReturn('someFakeCode');

        $this->filterFactory->method('create')->willReturn(new ModelFilter());

        $this->admin->method('getLabelTranslatorStrategy')->willReturn(new FormLabelTranslatorStrategy());

        $datagrid
            ->expects(static::once())
            ->method('addFilter')
            ->with(static::isInstanceOf(ModelFilter::class));

        $this->filterFactory
            ->expects(static::once())
            ->method('create')
            ->with('test', ModelFilter::class);

        $this->datagridBuilder->addFilter(
            $datagrid,
            null,
            $fieldDescription
        );

        static::assertSame('guess_value', $fieldDescription->getOption('guess_option'));
        static::assertSame(['guess_array_value'], $fieldDescription->getOption('guess_array_option'));
    }

    public function testAddFilterWithType(): void
    {
        $this->admin
            ->expects(static::once())
            ->method('addFilterFieldDescription');

        $datagrid = $this->createMock(DatagridInterface::class);

        $fieldDescription = new FieldDescription('test');
        $fieldDescription->setAdmin($this->admin);

        $this->filterFactory->method('create')->willReturn(new ModelFilter());

        $this->admin->method('getLabelTranslatorStrategy')->willReturn(new FormLabelTranslatorStrategy());

        $datagrid
            ->expects(static::once())
            ->method('addFilter')
            ->with(static::isInstanceOf(ModelFilter::class));

        $this->datagridBuilder->addFilter(
            $datagrid,
            ModelFilter::class,
            $fieldDescription
        );

        static::assertSame(ModelFilter::class, $fieldDescription->getType());
    }
}
