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

use Doctrine\Common\Inflector\Inflector;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription;

class FieldDescriptionTest extends TestCase
{
    public function testOptions()
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

    public function testAssociationMapping()
    {
        $field = new FieldDescription();
        $field->setAssociationMapping([
            'type' => 'integer',
            'fieldName' => 'position',
        ]);

        $this->assertSame('integer', $field->getType());
        $this->assertSame('position', $field->getFieldName());

        // cannot overwrite defined definition
        $field->setAssociationMapping([
            'type' => 'overwrite?',
            'fieldName' => 'overwritten',
        ]);

        $this->assertSame('integer', $field->getType());
        $this->assertSame('overwritten', $field->getFieldName());

        $field->setMappingType('string');
        $this->assertSame('string', $field->getMappingType());
        $this->assertSame('integer', $field->getType());
    }

    public function testCamelize()
    {
        $this->assertSame('FooBar', Inflector::classify('foo_bar'));
        $this->assertSame('FooBar', Inflector::classify('foo bar'));
        $this->assertSame('FOoBar', Inflector::classify('fOo bar'));
    }

    public function testSetName()
    {
        $field = new FieldDescription();
        $field->setName('New field description name');

        $this->assertSame($field->getName(), 'New field description name');
    }

    public function testSetNameSetFieldNameToo()
    {
        $field = new FieldDescription();
        $field->setName('New field description name');

        $this->assertSame($field->getFieldName(), 'New field description name');
    }

    public function testSetNameDoesNotSetFieldNameWhenSetBefore()
    {
        $field = new FieldDescription();
        $field->setFieldName('field name');
        $field->setName('New field description name');

        $this->assertSame($field->getFieldName(), 'field name');
    }

    public function testGetParent()
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $field = new FieldDescription();
        $field->setParent($adminMock);

        $this->assertSame($adminMock, $field->getParent());
    }

    public function testGetHelp()
    {
        $field = new FieldDescription();
        $field->setHelp('help message');

        $this->assertSame($field->getHelp(), 'help message');
    }

    public function testGetAdmin()
    {
        $adminMock = $this->createMock(AdminInterface::class);
        $field = new FieldDescription();
        $field->setAdmin($adminMock);

        $this->assertSame($adminMock, $field->getAdmin());
    }

    public function testGetAssociationAdmin()
    {
        $adminMock = $this->createMock(AbstractAdmin::class);
        $adminMock->expects($this->once())
            ->method('setParentFieldDescription')
            ->with($this->isInstanceOf(FieldDescriptionInterface::class));

        $field = new FieldDescription();
        $field->setAssociationAdmin($adminMock);

        $this->assertSame($adminMock, $field->getAssociationAdmin());
    }

    public function testHasAssociationAdmin()
    {
        $adminMock = $this->createMock(AbstractAdmin::class);
        $adminMock->expects($this->once())
            ->method('setParentFieldDescription')
            ->with($this->isInstanceOf(FieldDescriptionInterface::class));

        $field = new FieldDescription();

        $this->assertFalse($field->hasAssociationAdmin());

        $field->setAssociationAdmin($adminMock);

        $this->assertTrue($field->hasAssociationAdmin());
    }

    public function testGetValue()
    {
        $mockedObject = $this->getMockBuilder('MockedTestObject')
            ->setMethods(['myMethod'])
            ->getMock();
        $mockedObject->expects($this->once())
            ->method('myMethod')
            ->willReturn('myMethodValue');

        $field = new FieldDescription();
        $field->setOption('code', 'myMethod');

        $this->assertSame($field->getValue($mockedObject), 'myMethodValue');
    }

    public function testGetValueWhenCannotRetrieve()
    {
        $this->expectException(\Sonata\AdminBundle\Exception\NoValueException::class);

        $mockedObject = $this->getMockBuilder('MockedTestObject')
            ->setMethods(['myMethod'])
            ->getMock();
        $mockedObject->expects($this->never())
            ->method('myMethod')
            ->willReturn('myMethodValue');

        $field = new FieldDescription();

        $this->assertSame($field->getValue($mockedObject), 'myMethodValue');
    }

    public function testGetAssociationMapping()
    {
        $assocationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setAssociationMapping($assocationMapping);

        $this->assertSame($assocationMapping, $field->getAssociationMapping());
    }

    public function testSetAssociationMappingAllowOnlyForArray()
    {
        $this->expectException(\RuntimeException::class);

        $field = new FieldDescription();
        $field->setAssociationMapping('test');
    }

    public function testSetFieldMappingAllowOnlyForArray()
    {
        $this->expectException(\RuntimeException::class);

        $field = new FieldDescription();
        $field->setFieldMapping('test');
    }

    public function testSetFieldMappingSetType()
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertSame('integer', $field->getType());
    }

    public function testSetFieldMappingSetMappingType()
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertSame('integer', $field->getMappingType());
    }

    public function testSetFieldMappingSetFieldName()
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertSame('position', $field->getFieldName());
    }

    public function testGetTargetEntity()
    {
        $assocationMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'targetDocument' => 'someValue',
        ];

        $field = new FieldDescription();

        $this->assertNull($field->getTargetEntity());

        $field->setAssociationMapping($assocationMapping);

        $this->assertSame('someValue', $field->getTargetEntity());
    }

    public function testIsIdentifierFromFieldMapping()
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => 'someId',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertSame('someId', $field->isIdentifier());
    }

    public function testGetFieldMapping()
    {
        $fieldMapping = [
            'type' => 'integer',
            'fieldName' => 'position',
            'id' => 'someId',
        ];

        $field = new FieldDescription();
        $field->setFieldMapping($fieldMapping);

        $this->assertSame($fieldMapping, $field->getFieldMapping());
    }
}
