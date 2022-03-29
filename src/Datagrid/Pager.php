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

namespace Sonata\DoctrineMongoDBAdminBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\Pager as BasePager;

/**
 * Doctrine pager class.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Kévin Dunglas <dunglas@gmail.com>
 *
 * @phpstan-extends BasePager<ProxyQueryInterface>
 */
final class Pager extends BasePager
{
    /**
     * @var int
     */
    private $resultsCount = 0;

    public function countResults(): int
    {
        return $this->resultsCount;
    }

    public function getCurrentPageResults(): iterable
    {
        $query = $this->getQuery();

        if (null === $query) {
            throw new \RuntimeException('Uninitialized query.');
        }

        return $query->execute();
    }

    public function init(): void
    {
        $query = $this->getQuery();

        if (null === $query) {
            throw new \RuntimeException('Uninitialized query.');
        }

        $this->resultsCount = $this->computeResultsCount($query);

        $query->setFirstResult(0);
        $query->setMaxResults(0);

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage()) {
            $this->setLastPage(0);
        } elseif (0 === $this->countResults()) {
            $this->setLastPage(1);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage((int) ceil($this->countResults() / $this->getMaxPerPage()));

            $query->setFirstResult($offset);
            $query->setMaxResults($this->getMaxPerPage());
        }
    }

    private function computeResultsCount(ProxyQueryInterface $query): int
    {
        $countQuery = clone $query;

        $result = $countQuery->getQueryBuilder()->count()->getQuery()->execute();

        \assert(\is_int($result));

        return $result;
    }
}
