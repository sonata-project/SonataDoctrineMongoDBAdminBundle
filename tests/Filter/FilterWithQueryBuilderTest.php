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

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use PHPUnit\Framework\TestCase;

abstract class FilterWithQueryBuilderTest extends TestCase
{
    private $queryBuilder;
    private $expr;

    public function setUp(): void
    {
        $this->queryBuilder = $this->createMock(Builder::class);
        $this->queryBuilder
            ->method('field')
            ->with('field')
            ->willReturnSelf()
        ;
        $this->expr = $this->createMock(Expr::class);
        $this->expr
            ->method('field')
            ->willReturnSelf()
        ;
        $this->queryBuilder
            ->method('expr')
            ->willReturn($this->expr)
        ;
    }

    protected function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
