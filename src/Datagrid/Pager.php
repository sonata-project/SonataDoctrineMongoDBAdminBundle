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
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class Pager extends BasePager
{
    protected $queryBuilder = null;

    /**
     * @var int
     */
    private $resultsCount = 0;

    public function countResults(): int
    {
        // NEXT_MAJOR: just return "$this->resultsCount" directly.
        $deprecatedCount = $this->getNbResults('sonata_deprecation_mute');

        if ($deprecatedCount === $this->resultsCount) {
            return $this->resultsCount;
        }

        @trigger_error(sprintf(
            'Relying on the protected property "%s::$nbResults" and its getter/setter is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and will fail 4.0. Use "countResults()" and "setResultsCount()" instead.',
            self::class,
        ), E_USER_DEPRECATED);

        return $deprecatedCount;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x
     */
    public function getNbResults(): int
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The %s() method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and will be removed in 4.0. Use "countResults()" instead.',
                __METHOD__,
            ), E_USER_DEPRECATED);
        }

        return $this->nbResults;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x
     *
     * @return int
     */
    public function computeNbResult()
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[0] ?? null)) {
            @trigger_error(sprintf(
                'The %s() method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and will be removed in 4.0.',
                __METHOD__,
            ), E_USER_DEPRECATED);
        }

        return $this->computeResultsCount();
    }

    public function getResults(): array
    {
        return $this->getQuery()->execute()->toArray();
    }

    public function init(): void
    {
        // NEXT_MAJOR: Remove next line.
        $this->resetIterator('sonata_deprecation_mute');

        // NEXT_MAJOR: Remove next line and uncomment the next one.
        $this->setResultsCount($this->computeNbResult('sonata_deprecation_mute'));
        // $this->setResultsCount($this->computeResultsCount());

        $this->getQuery()->setFirstResult(0);
        $this->getQuery()->setMaxResults(0);

        // NEXT_MAJOR: Remove this block.
        if (\count($this->getParameters('sonata_deprecation_mute')) > 0) {
            $this->getQuery()->setParameters($this->getParameters('sonata_deprecation_mute'));
        }

        if (0 === $this->getPage() || 0 === $this->getMaxPerPage() || 0 === $this->countResults()) {
            $this->setLastPage(0);
        } else {
            $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

            $this->setLastPage((int) ceil($this->countResults() / $this->getMaxPerPage()));

            $this->getQuery()->setFirstResult($offset);
            $this->getQuery()->setMaxResults($this->getMaxPerPage());
        }
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x
     */
    protected function setNbResults(int $nb): void
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[1] ?? null)) {
            @trigger_error(sprintf(
                'The %s() method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and will be removed in 4.0. Use "setResultsCount()" instead.',
                __METHOD__,
            ), E_USER_DEPRECATED);
        }

        $this->nbResults = $nb;
        $this->resultsCount = $nb;
    }

    private function computeResultsCount(): int
    {
        $countQuery = clone $this->getQuery();

        // NEXT_MAJOR: Remove this block.
        if (\count($this->getParameters('sonata_deprecation_mute')) > 0) {
            $countQuery->setParameters($this->getParameters('sonata_deprecation_mute'));
        }

        return (int) $countQuery->count()->getQuery()->execute();
    }

    private function setResultsCount(int $count): void
    {
        $this->resultsCount = $count;
        // NEXT_MAJOR: Remove this line.
        $this->setNbResults($count, 'sonata_deprecation_mute');
    }
}
