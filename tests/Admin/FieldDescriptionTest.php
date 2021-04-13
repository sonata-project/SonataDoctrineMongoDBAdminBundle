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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription;

final class FieldDescriptionTest extends TestCase
{
    public function testAssociationMapping(): void
    {
        $field = new FieldDescription(
            'name',
            [],
            [],
            [
                'type' => 'integer',
                'fieldName' => 'position',
            ]
        );

        $this->assertSame('position', $field->getFieldName());
    }

    public function testGetAssociationMapping(): void
    {
        $associationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('name', [], [], $associationMapping);

        $this->assertSame($associationMapping, $field->getAssociationMapping());
    }

    public function testSetFieldMappingSetMappingType(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        $this->assertSame('integer', $field->getMappingType());
    }

    public function testSetFieldMappingSetFieldName(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('position', [], $fieldMapping);

        $this->assertSame('position', $field->getFieldName());
    }

    public function testGetTargetModel(): void
    {
        $associationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'targetDocument' => 'someValue',
        ];

        $field = new FieldDescription('name');

        $this->assertNull($field->getTargetModel());

        $field = new FieldDescription('name', [], [], $associationMapping);

        $this->assertSame('someValue', $field->getTargetModel());
    }

    public function testIsIdentifierFromFieldMapping(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => true,
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        $this->assertTrue($field->isIdentifier());
    }

    public function testGetFieldMapping(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => 'someId',
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        $this->assertSame($fieldMapping, $field->getFieldMapping());
    }

    public function testGetParentValue(): void
    {
        $parentAssociationMappings = [
            ['fieldName' => 'parent'],
        ];

        $field = new FieldDescription('name', [], [], [], $parentAssociationMappings);

        $dummyParent = new class() {
            public function name(): string
            {
                return 'hi';
            }
        };

        $dummyChild = new class($dummyParent) {
            /** @var object */
            private $parent;

            public function __construct(object $parent)
            {
                $this->parent = $parent;
            }

            public function parent(): object
            {
                return $this->parent;
            }
        };

        $this->assertSame('hi', $field->getValue($dummyChild));
    }
}
