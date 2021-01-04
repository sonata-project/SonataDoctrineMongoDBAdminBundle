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
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;

class FieldDescriptionTest extends TestCase
{
    public function testOptions(): void
    {
        $field = new FieldDescription('name');
        $field->setOptions([
            'template' => 'foo',
            'type' => 'bar',
            'misc' => 'foobar',
        ]);

        // test method shortcut
        $this->assertNull($field->getOption('template'));
        $this->assertNull($field->getOption('type'));

        $this->assertSame('foo', $field->getTemplate());
        $this->assertSame('bar', $field->getType());

        // test the default value option
        $this->assertSame('default', $field->getOption('template', 'default'));

        // test the merge options
        $field->setOption('array', ['key1' => 'val1']);
        $field->mergeOption('array', ['key1' => 'key_1', 'key2' => 'key_2']);

        $this->assertSame(['key1' => 'key_1', 'key2' => 'key_2'], $field->getOption('array'));

        $field->mergeOption('non_existant', ['key1' => 'key_1', 'key2' => 'key_2']);

        $this->assertSame(['key1' => 'key_1', 'key2' => 'key_2'], $field->getOption('array'));

        $field->mergeOptions(['array' => ['key3' => 'key_3']]);

        $this->assertSame(['key1' => 'key_1', 'key2' => 'key_2', 'key3' => 'key_3'], $field->getOption('array'));

        $field->setOption('integer', 1);

        try {
            $field->mergeOption('integer', []);
            $this->fail('no exception raised !!');
        } catch (\RuntimeException $e) {
        }

        $field->mergeOptions(['final' => 'test']);

        $expected = [
          'misc' => 'foobar',
          'placeholder' => 'short_object_description_placeholder',
          'link_parameters' => [],
          'array' => [
            'key1' => 'key_1',
            'key2' => 'key_2',
            'key3' => 'key_3',
          ],
          'non_existant' => [
            'key1' => 'key_1',
            'key2' => 'key_2',
          ],
          'integer' => 1,
          'final' => 'test',
        ];

        $this->assertSame($expected, $field->getOptions());
    }

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

        $this->assertSame('integer', $field->getType());
        $this->assertSame('position', $field->getFieldName());

        // cannot overwrite defined definition
        // NEXT_MAJOR: Remove this call.
        $field->setAssociationMapping([
            'type' => 'overwrite?',
            'fieldName' => 'overwritten',
        ]);

        // NEXT_MAJOR: Remove following three lines.
        $this->assertSame('integer', $field->getType());
        $this->assertSame('overwritten', $field->getFieldName());
        $this->assertSame('integer', $field->getType());
    }

    public function testSetName(): void
    {
        $field = new FieldDescription('name');
        $field->setName('New field description name');

        $this->assertSame($field->getName(), 'New field description name');
    }

    public function testSetNameSetFieldNameToo(): void
    {
        $field = new FieldDescription('New field description name');

        $this->assertSame($field->getFieldName(), 'New field description name');
    }

    public function testSetNameDoesNotSetFieldNameWhenSetBefore(): void
    {
        $field = new FieldDescription('name');
        $field->setFieldName('field name');
        $field->setName('New field description name');

        $this->assertSame($field->getFieldName(), 'field name');
    }

    public function testGetParent(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $field = new FieldDescription('name');
        $field->setParent($adminMock);

        $this->assertSame($adminMock, $field->getParent());
    }

    public function testGetAdmin(): void
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $field = new FieldDescription('name');
        $field->setAdmin($adminMock);

        $this->assertSame($adminMock, $field->getAdmin());
    }

    public function testGetAssociationAdmin(): void
    {
        $adminMock = $this->createMock(AbstractAdmin::class);
        $adminMock->expects($this->once())
            ->method('setParentFieldDescription')
            ->with($this->isInstanceOf(FieldDescriptionInterface::class));

        $field = new FieldDescription('name');
        $field->setAssociationAdmin($adminMock);

        $this->assertSame($adminMock, $field->getAssociationAdmin());
    }

    public function testHasAssociationAdmin(): void
    {
        $adminMock = $this->createMock(AbstractAdmin::class);
        $adminMock->expects($this->once())
            ->method('setParentFieldDescription')
            ->with($this->isInstanceOf(FieldDescriptionInterface::class));

        $field = new FieldDescription('name');

        $this->assertFalse($field->hasAssociationAdmin());

        $field->setAssociationAdmin($adminMock);

        $this->assertTrue($field->hasAssociationAdmin());
    }

    public function testGetValue(): void
    {
        $object = new class() {
            public function myMethod()
            {
                return 'myMethodValue';
            }
        };

        $field = new FieldDescription('name');
        $field->setOption('code', 'myMethod');

        $this->assertSame($field->getValue($object), 'myMethodValue');
    }

    public function testGetValueWhenCannotRetrieve(): void
    {
        $object = new class() {
            public function myMethod()
            {
                return 'myMethodValue';
            }
        };

        $field = new FieldDescription('name');

        $this->expectException(NoValueException::class);

        $this->assertSame($field->getValue($object), 'myMethodValue');
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

    public function testSetFieldMappingSetType(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription('name', [], $fieldMapping);

        $this->assertSame('integer', $field->getType());
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

        $this->assertNull($field->getTargetEntity());

        $field->setAssociationMapping($assocationMapping);

        $this->assertSame('someValue', $field->getTargetEntity());
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
}
