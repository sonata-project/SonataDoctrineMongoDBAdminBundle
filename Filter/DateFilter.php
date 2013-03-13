<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class DateFilter extends AbstractDateFilter
{
    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string                                           $field
     * @param array                                            $data
     */
    protected function applyTypeIsLessEqual(ProxyQueryInterface $queryBuilder, $field, $data)
    {
        $data['value']->add(new \DateInterval('P1D'));

        $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string                                           $field
     * @param array                                            $data
     */
    protected function applyTypeIsGreaterThan(ProxyQueryInterface $queryBuilder, $field, $data)
    {
        $data['value']->add(new \DateInterval('P1D'));

        $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * Because we lack a time variable we select a range from the days start to end.
     *
     * @author Wesley van Opdorp <wesley.van.opdorp@freshheads.com>
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string                                           $field
     * @param array                                            $data
     */
    protected function applyTypeIsEqual(ProxyQueryInterface $queryBuilder, $field, $data)
    {
        $end = clone $data['value'];
        $end->add(new \DateInterval('P1D'));

        $queryBuilder->field($field)->range($data['value'], $end);
    }

}
