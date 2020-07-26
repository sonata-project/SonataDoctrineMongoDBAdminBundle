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
use PHPUnit\Framework\TestCase;
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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class DatagridBuilderTest extends TestCase
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
        $this->filterFactory = $this->createStub(FilterFactoryInterface::class);
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
        $classMetadata = $this->createMock(ClassMetadata::class);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('test');
        $fieldDescription->setMappingType(ClassMetadata::ONE);

        $this->admin
            ->expects($this->once())
            ->method('attachAdminClass');

        $this->modelManager->method('hasMetadata')->willReturn(true);

        $this->modelManager
            ->expects($this->once())
            ->method('getParentMetadataForProperty')
            ->willReturn([$classMetadata, 'someField', $parentAssociationMapping = []]);

        $this->datagridBuilder->fixFieldDescription($this->admin, $fieldDescription);
    }

    public function testAddFilterNoType(): void
    {
        $this->admin
            ->expects($this->once())
            ->method('addFilterFieldDescription');

        $datagrid = $this->createMock(DatagridInterface::class);
        $guessType = new TypeGuess(ModelFilter::class, [
            'name' => 'value',
        ], Guess::VERY_HIGH_CONFIDENCE);

        $fieldDescription = new FieldDescription();
        $fieldDescription->setName('test');

        $this->typeGuesser->method('guessType')->willReturn($guessType);

        $this->modelManager
            ->expects($this->once())
            ->method('hasMetadata')->willReturn(false);

        $this->admin->method('getCode')->willReturn('someFakeCode');

        $this->filterFactory->method('create')->willReturn(new ModelFilter());

        $this->admin->method('getLabelTranslatorStrategy')->willReturn(new FormLabelTranslatorStrategy());

        $datagrid
            ->expects($this->once())
            ->method('addFilter')
            ->with($this->isInstanceOf(ModelFilter::class));

        $this->datagridBuilder->addFilter(
            $datagrid,
            null,
            $fieldDescription,
            $this->admin
        );
    }
}
