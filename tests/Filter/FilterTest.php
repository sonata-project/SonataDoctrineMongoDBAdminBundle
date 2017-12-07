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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Filter;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Filter\Filter;

class FilterTest_Filter extends Filter
{
    /**
     * Apply the filter to the QueryBuilder instance.
     *
     * @param $queryBuilder
     * @param string $alias
     * @param string $field
     * @param string $value
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value): void
    {
        // TODO: Implement filter() method.
    }

    public function getDefaultOptions()
    {
        return ['option1' => 2];
    }

    public function getRenderSettings()
    {
        return ['sonata_type_filter_default', [
            'type' => $this->getFieldType(),
            'options' => $this->getFieldOptions(),
        ]];
    }

    public function testAssociation(ProxyQueryInterface $queryBuilder, $value)
    {
        return $this->association($queryBuilder, $value);
    }
}

class FilterTest extends TestCase
{
    public function testFieldDescription(): void
    {
        $filter = new FilterTest_Filter();
        $this->assertEquals(['option1' => 2], $filter->getDefaultOptions());
        $this->assertNull($filter->getOption('1'));

        $filter->initialize('field_name', ['field_options' => ['class' => 'FooBar']]);

        $this->assertEquals(2, $filter->getOption('option1'));
        $this->assertNull($filter->getOption('foo'));
        $this->assertEquals('bar', $filter->getOption('foo', 'bar'));

        $this->assertEquals('field_name', $filter->getName());
        $this->assertEquals(
            method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\TextType'
            : 'text',
            $filter->getFieldType()
        );
        $this->assertEquals(['class' => 'FooBar'], $filter->getFieldOptions());
    }

    public function testValues(): void
    {
        $filter = new FilterTest_Filter();
        $this->assertEmpty($filter->getValue());

        $filter->setValue(42);
        $this->assertEquals(42, $filter->getValue());
    }

    public function testExceptionOnEmptyFieldName(): void
    {
        $this->expectException(\RuntimeException::class);

        $filter = new FilterTest_Filter();
        $filter->getFieldName();
    }

    public function testIsActive(): void
    {
        $filter = new FilterTest_Filter();
        $this->assertFalse($filter->isActive());
    }
}
