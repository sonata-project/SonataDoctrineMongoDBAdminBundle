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
        return $this->getQuery()->execute();
    }

    public function init(): void
    {
        $this->resultsCount = $this->computeResultsCount();

        $this->getQuery()->setFirstResult(0);
        $this->getQuery()->setMaxResults(0);

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage() || 0 === $this->countResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage((int) ceil($this->countResults() / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($offset);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }

    private function computeResultsCount(): int
    {
        $countQuery = clone $this->getQuery();

        return (int) $countQuery->count()->getQuery()->execute();
    }
}
