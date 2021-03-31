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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class FilterWithQueryBuilderTest extends TestCase
{
    protected const DEFAULT_FIELD_NAME = 'field';

    /**
     * @var Builder&MockObject
     */
    private $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(Builder::class);
        $this->queryBuilder
            ->method('field')
            ->with(self::DEFAULT_FIELD_NAME)
            ->willReturnSelf();
        $expr = $this->createMock(Expr::class);
        $expr
            ->method('field')
            ->willReturnSelf();
        $this->queryBuilder
            ->method('expr')
            ->willReturn($expr);
    }

    /**
     * @return Builder&MockObject
     */
    protected function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
