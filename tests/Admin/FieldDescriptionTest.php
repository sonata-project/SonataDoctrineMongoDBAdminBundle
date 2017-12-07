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
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;

class FieldDescriptionTest extends TestCase
{
    public function testOptions(): void
    {
        $field = new FieldDescription();
        $field->setOptions([
            'template' => 'foo',
            'type' => 'bar',
            'misc' => 'foobar',
        ]);

        // test method shortcut
        $this->assertNull($field->getOption('template'));
        $this->assertNull($field->getOption('type'));

        $this->assertEquals('foo', $field->getTemplate());
        $this->assertEquals('bar', $field->getType());

        // test the default value option
        $this->assertEquals('default', $field->getOption('template', 'default'));

        // test the merge options
        $field->setOption('array', ['key1' => 'val1']);
        $field->mergeOption('array', ['key1' => 'key_1', 'key2' => 'key_2']);

        $this->assertEquals(['key1' => 'key_1', 'key2' => 'key_2'], $field->getOption('array'));

        $field->mergeOption('non_existant', ['key1' => 'key_1', 'key2' => 'key_2']);

        $this->assertEquals(['key1' => 'key_1', 'key2' => 'key_2'], $field->getOption('array'));

        $field->mergeOptions(['array' => ['key3' => 'key_3']]);

        $this->assertEquals(['key1' => 'key_1', 'key2' => 'key_2', 'key3' => 'key_3'], $field->getOption('array'));

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
          'link_parameters' => [],
        ];

        $this->assertEquals($expected, $field->getOptions());
    }

    public function testAssociationMapping(): void
    {
        $field = new FieldDescription();
        $field->setAssociationMapping([
            'type' => 'integer',
            'fieldName' => 'position',
        ]);

        $this->assertEquals('integer', $field->getType());
        $this->assertEquals('position', $field->getFieldName());

        // cannot overwrite defined definition
        $field->setAssociationMapping([
            'type' => 'overwrite?',
            'fieldName' => 'overwritten',
        ]);

        $this->assertEquals('integer', $field->getType());
        $this->assertEquals('overwritten', $field->getFieldName());

        $field->setMappingType('string');
        $this->assertEquals('string', $field->getMappingType());
        $this->assertEquals('integer', $field->getType());
    }

    public function testCamelize(): void
    {
        $this->assertEquals('FooBar', FieldDescription::camelize('foo_bar'));
        $this->assertEquals('FooBar', FieldDescription::camelize('foo bar'));
        $this->assertEquals('FOoBar', FieldDescription::camelize('fOo bar'));
    }

    public function testSetName(): void
    {
        $field = new FieldDescription();
        $field->setName('New field description name');

        $this->assertEquals($field->getName(), 'New field description name');
    }

    public function testSetNameSetFieldNameToo(): void
    {
        $field = new FieldDescription();
        $field->setName('New field description name');

        $this->assertEquals($field->getFieldName(), 'New field description name');
    }

    public function testSetNameDoesNotSetFieldNameWhenSetBefore(): void
    {
        $field = new FieldDescription();
        $field->setFieldName('field name');
        $field->setName('New field description name');

        $this->assertEquals($field->getFieldName(), 'field name');
    }

    public function testGetParent(): void
    {
        $adminMock = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $field = new FieldDescription();
        $field->setParent($adminMock);

        $this->assertSame($adminMock, $field->getParent());
    }

    public function testGetHelp(): void
    {
        $field = new FieldDescription();
        $field->setHelp('help message');

        $this->assertEquals($field->getHelp(), 'help message');
    }

    public function testGetAdmin(): void
    {
        $adminMock = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $field = new FieldDescription();
        $field->setAdmin($adminMock);

        $this->assertSame($adminMock, $field->getAdmin());
    }

    public function testGetAssociationAdmin(): void
    {
        $adminMock = $this->createMock('Sonata\AdminBundle\Admin\Admin');
        $adminMock->expects($this->once())
            ->method('setParentFieldDescription')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface'));

        $field = new FieldDescription();
        $field->setAssociationAdmin($adminMock);

        $this->assertSame($adminMock, $field->getAssociationAdmin());
    }

    public function testHasAssociationAdmin(): void
    {
        $adminMock = $this->createMock('Sonata\AdminBundle\Admin\Admin');
        $adminMock->expects($this->once())
            ->method('setParentFieldDescription')
            ->with($this->isInstanceOf('Sonata\AdminBundle\Admin\FieldDescriptionInterface'));

        $field = new FieldDescription();

        $this->assertFalse($field->hasAssociationAdmin());

        $field->setAssociationAdmin($adminMock);

        $this->assertTrue($field->hasAssociationAdmin());
    }

    public function testGetValue(): void
    {
        $mockedObject = $this->getMockBuilder('MockedTestObject')
            ->setMethods(['myMethod'])
            ->getMock();
        $mockedObject->expects($this->once())
            ->method('myMethod')
            ->will($this->returnValue('myMethodValue'));

        $field = new FieldDescription();
        $field->setOption('code', 'myMethod');

        $this->assertEquals($field->getValue($mockedObject), 'myMethodValue');
    }

    public function testGetValueWhenCannotRetrieve(): void
    {
        $this->expectException(\Sonata\AdminBundle\Exception\NoValueException::class);

        $mockedObject = $this->getMockBuilder('MockedTestObject')
            ->setMethods(['myMethod'])
            ->getMock();
        $mockedObject->expects($this->never())
            ->method('myMethod')
            ->will($this->returnValue('myMethodValue'));

        $field = new FieldDescription();

        $this->assertEquals($field->getValue($mockedObject), 'myMethodValue');
    }

    public function testGetAssociationMapping(): void
    {
        $assocationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setAssociationMapping($assocationMapping);

        $this->assertEquals($assocationMapping, $field->getAssociationMapping());
    }

    public function testSetAssociationMappingAllowOnlyForArray(): void
    {
        $this->expectException(\RuntimeException::class);

        $field = new FieldDescription();
        $field->setAssociationMapping('test');
    }

    public function testSetFieldMappingAllowOnlyForArray(): void
    {
        $this->expectException(\RuntimeException::class);

        $field = new FieldDescription();
        $field->setFieldMapping('test');
    }

    public function testSetFieldMappingSetType(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertEquals('integer', $field->getType());
    }

    public function testSetFieldMappingSetMappingType(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertEquals('integer', $field->getMappingType());
    }

    public function testSetFieldMappingSetFieldName(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertEquals('position', $field->getFieldName());
    }

    public function testGetTargetEntity(): void
    {
        $assocationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'targetDocument' => 'someValue',
        ];

        $field = new FieldDescription();

        $this->assertNull($field->getTargetEntity());

        $field->setAssociationMapping($assocationMapping);

        $this->assertEquals('someValue', $field->getTargetEntity());
    }

    public function testIsIdentifierFromFieldMapping(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => 'someId',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertEquals('someId', $field->isIdentifier());
    }

    public function testGetFieldMapping(): void
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => 'someId',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertEquals($fieldMapping, $field->getFieldMapping());
    }
}
