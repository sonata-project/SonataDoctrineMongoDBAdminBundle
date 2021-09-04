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

class FieldDescriptionTest extends TestCase
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

        static::assertSame('integer', $field->getType());
        static::assertSame('position', $field->getFieldName());

        // cannot overwrite defined definition
        // NEXT_MAJOR: Remove this call.
        $field->setAssociationMapping([
            'type' => 'overwrite?',
            'fieldName' => 'overwritten',
        ]);

        // NEXT_MAJOR: Remove following three lines.
        static::assertSame('integer', $field->getType());
        static::assertSame('overwritten', $field->getFieldName());
        static::assertSame('integer', $field->getType());
    }

    public function testGetAssociationMapping(): void
    {
        $associationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('name', [], [], $associationMapping);

        static::assertSame($associationMapping, $field->getAssociationMapping());
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function testSetAssociationMappingAllowOnlyForArray(): void
    {
        $this->expectException(\RuntimeException::class);

        $field = new FieldDescription('name');
        $field->setAssociationMapping('test');
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function testSetFieldMappingAllowOnlyForArray(): void
    {
        $this->expectException(\RuntimeException::class);

        $field = new FieldDescription('name');
        $field->setFieldMapping('test');
    }

    public function testSetFieldMappingSetType(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        static::assertSame('integer', $field->getType());
    }

    public function testSetFieldMappingSetMappingType(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        static::assertSame('integer', $field->getMappingType());
    }

    public function testSetFieldMappingSetFieldName(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('position', [], $fieldMapping);

        static::assertSame('position', $field->getFieldName());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetTargetEntity(): void
    {
        $assocationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'targetDocument' => 'someValue',
        ];

        $field = new FieldDescription('name');

        static::assertNull($field->getTargetEntity());

        $field->setAssociationMapping($assocationMapping);

        static::assertSame('someValue', $field->getTargetEntity());
    }

    public function testGetTargetModel(): void
    {
        $associationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'targetDocument' => 'someValue',
        ];

        $field = new FieldDescription('name');

        static::assertNull($field->getTargetModel());

        $field = new FieldDescription('name', [], [], $associationMapping);

        static::assertSame('someValue', $field->getTargetModel());
    }

    public function testIsIdentifierFromFieldMapping(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => true,
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        static::assertTrue($field->isIdentifier());
    }

    public function testGetFieldMapping(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => 'someId',
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        static::assertSame($fieldMapping, $field->getFieldMapping());
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

        static::assertSame('hi', $field->getValue($dummyChild));
    }
}
