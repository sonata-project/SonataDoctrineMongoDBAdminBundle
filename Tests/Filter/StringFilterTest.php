<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Filter;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineMongoDBAdminBundle\Filter\Filter;
use Sonata\DoctrineMongoDBAdminBundle\Filter\StringFilter;

class StringFilterTest extends FilterWithQueryBuilderTest
{
    public function testEmpty()
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', array('field_options' => array('class' => 'FooBar')));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', null);
        $filter->filter($builder, 'alias', 'field', '');

        $this->assertEquals(false, $filter->isActive());
    }

    public function testContains()
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', array('format' => '%s'));

        $builder = new ProxyQuery($this->getQueryBuilder());
        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_CONTAINS));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => null));
        $this->assertEquals(true, $filter->isActive());
    }

    public function testNotContains()
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', array('format' => '%s'));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_NOT_CONTAINS));
        $this->assertEquals(true, $filter->isActive());
    }

    public function testEquals()
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', array('format' => '%s'));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_EQUAL));
        $this->assertEquals(true, $filter->isActive());
    }

    public function testEqualsWithValidParentAssociationMappings()
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', array(
            'format' => '%s',
            'field_name' => 'field_name',
            'parent_association_mappings' => array(
                array(
                    'fieldName' => 'association_mapping',
                ),
                array(
                    'fieldName' => 'sub_association_mapping',
                ),
                array(
                    'fieldName' => 'sub_sub_association_mapping',
                ),
            ),
        ));

        $builder = new ProxyQuery($this->getQueryBuilder());

        $filter->apply($builder, array('type' => ChoiceType::TYPE_EQUAL, 'value' => 'asd'));
        $this->assertEquals(true, $filter->isActive());
    }

    public function testOr()
    {
        $filter = new StringFilter();
        $filter->initialize('field_name', array('format' => '%s'));
        $filter->setCondition(Filter::CONDITION_OR);

        $builder = new ProxyQuery($this->getQueryBuilder());
        $builder->getQueryBuilder()->expects($this->once())->method('addOr');
        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_CONTAINS));
        $this->assertEquals(true, $filter->isActive());

        $filter->setCondition(Filter::CONDITION_AND);

        $builder = new ProxyQuery($this->getQueryBuilder());
        $builder->getQueryBuilder()->expects($this->never())->method('addOr');
        $filter->filter($builder, 'alias', 'field', array('value' => 'asd', 'type' => ChoiceType::TYPE_CONTAINS));
        $this->assertEquals(true, $filter->isActive());
    }
}
