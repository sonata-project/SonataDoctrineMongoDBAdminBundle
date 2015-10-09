<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Filter;

abstract class FilterWithQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $queryBuilder = null;
    private $expr = null;

    public function setUp()
    {
        $this->queryBuilder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
                ->disableOriginalConstructor()
                ->getMock();
        $this->queryBuilder
                ->expects($this->any())
                ->method('field')
                ->will($this->returnSelf())
        ;
        $this->expr = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();
        $this->expr
            ->expects($this->any())
            ->method('field')
            ->will($this->returnSelf())
        ;
        $this->queryBuilder
            ->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($this->expr))
        ;
    }

    protected function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
