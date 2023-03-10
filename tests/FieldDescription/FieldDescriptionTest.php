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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\FieldDescription;

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

        static::assertSame('integer', $field->getMappingType());
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

    public function testSetFieldMappingSetMappingType(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        static::assertSame('integer', $field->getMappingType());
    }

    public function testGetTargetModel(): void
    {
        $associationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'targetDocument' => \stdClass::class,
        ];

        $field = new FieldDescription('name');

        static::assertNull($field->getTargetModel());

        $field = new FieldDescription('name', [], [], $associationMapping);

        static::assertSame(\stdClass::class, $field->getTargetModel());
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
            public function __construct(private object $parent)
            {
            }

            public function parent(): object
            {
                return $this->parent;
            }
        };

        static::assertSame('hi', $field->getValue($dummyChild));
    }
}
