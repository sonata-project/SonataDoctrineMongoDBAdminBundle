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

class FilterWithQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $queryBuilder = null;

    public function setUp()
    {
        $this->queryBuilder = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilder
            ->expects($this->any())
            ->method('field')
            ->will($this->returnSelf());
        $this->queryBuilder
            ->expects($this->any())
            ->method('expr')
            ->will($this->returnSelf());
        $this->queryBuilder
            ->expects($this->any())
            ->method('addOr')
            ->will($this->returnSelf());
        $this->queryBuilder
            ->expects($this->any())
            ->method('addAnd')
            ->will($this->returnSelf());
    }

    protected function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

}
