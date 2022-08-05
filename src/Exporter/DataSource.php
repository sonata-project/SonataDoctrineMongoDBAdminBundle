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

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\Exporter\Source\DoctrineODMQuerySourceIterator;

final class DataSource implements DataSourceInterface
{
    public function createIterator(BaseProxyQueryInterface $query, array $fields): \Iterator
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(sprintf(
                'Argument 1 passed to "%s()" MUST be an instance of "%s", instance of "%s" given.',
                __METHOD__,
                ProxyQueryInterface::class,
                \get_class($query)
            ));
        }

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        return new DoctrineODMQuerySourceIterator($query->getQueryBuilder()->getQuery(), $fields);
    }
}
