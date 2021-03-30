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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\DoctrineMongoDBAdminBundle\Builder\FormContractor;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;
use Sonata\DoctrineMongoDBAdminBundle\Tests\AbstractModelManagerTestCase;
use Sonata\DoctrineMongoDBAdminBundle\Tests\Fixtures\Document\DocumentWithReferences;
use Symfony\Component\Form\FormFactoryInterface;

final class FormContractorTest extends AbstractModelManagerTestCase
{
    /**
     * @var FormFactoryInterface&MockObject
     */
    private $formFactory;

    /**
     * @var FormContractor
     */
    private $formContractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formFactory = $this->createMock(FormFactoryInterface::class);

        $this->formContractor = new FormContractor($this->formFactory);
    }

    public function testFixFieldDescriptionForFieldMapping(): void
    {
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $admin = $this->createMock(AdminInterface::class);

        $fieldDescription = new FieldDescription('name', [], $classMetadata->fieldMappings['name']);
        $fieldDescription->setAdmin($admin);

        $this->formContractor->fixFieldDescription($fieldDescription);

        $this->assertSame($classMetadata->fieldMappings['name'], $fieldDescription->getFieldMapping());
    }

    public function testFixFieldDescriptionForAssociationMapping(): void
    {
        $classMetadata = $this->getMetadataForDocumentWithAnnotations(DocumentWithReferences::class);

        $admin = $this->createMock(AdminInterface::class);

        $fieldDescription = new FieldDescription(
            'embeddedDocument',
            [],
            $classMetadata->fieldMappings['embeddedDocument'],
            $classMetadata->associationMappings['embeddedDocument']
        );
        $fieldDescription->setAdmin($admin);

        $this->formContractor->fixFieldDescription($fieldDescription);

        $this->assertSame($classMetadata->associationMappings['embeddedDocument'], $fieldDescription->getAssociationMapping());
    }
}
