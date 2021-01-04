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

namespace Sonata\DoctrineMongoDBAdminBundle\Exporter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;
use Sonata\Exporter\Source\DoctrineODMQuerySourceIterator;
use Sonata\Exporter\Source\SourceIteratorInterface;

final class DataSource implements DataSourceInterface
{
    public function createIterator(ProxyQueryInterface $query, array $fields): SourceIteratorInterface
    {
        if (!$query instanceof ProxyQuery) {
            throw new \LogicException(sprintf('Argument 1 MUST be an instance of "%s"', ProxyQuery::class));
        }

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        return new DoctrineODMQuerySourceIterator($query->getQueryBuilder()->getQuery(), $fields);
    }
}
