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

abstract class FilterWithQueryBuilderTest extends TestCase
{
    private $queryBuilder = null;
    private $expr = null;

    public function setUp()
    {
        $this->queryBuilder = $this->createMock('Doctrine\ODM\MongoDB\Query\Builder');
        $this->queryBuilder
                ->expects($this->any())
                ->method('field')
                ->will($this->returnSelf())
        ;
        $this->expr = $this->createMock('Doctrine\ODM\MongoDB\Query\Expr');
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
