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

class DateTimeFilter extends AbstractDateFilter
{
    /**
     * Flag indicating that filter will filter by datetime instead by date.
     *
     * @var bool
     */
    protected $time = true;

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string                                           $field
     * @param array                                            $data
     */
    protected function applyTypeIsLessEqual(ProxyQueryInterface $queryBuilder, $field, $data)
    {
        // Add a minute so less then equal selects all seconds.
        $data['value']->add(new \DateInterval('PT1M'));

        $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string                                           $field
     * @param array                                            $data
     */
    protected function applyTypeIsGreaterThan(ProxyQueryInterface $queryBuilder, $field, $data)
    {
        // Add 59 seconds so anything above the minute is selected
        $data['value']->add(new \DateInterval('PT59S'));

        $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * Because we lack a second variable we select a range covering the entire minute.
     *
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string                                           $field
     * @param array                                            $data
     */
    protected function applyTypeIsEqual(ProxyQueryInterface $queryBuilder, $field, $data)
    {
        /** @var \DateTime $end */
        $end = clone $data['value'];
        $end->add(new \DateInterval('PT1M'));

        $queryBuilder->field($field)->range($data['value'], $end);
    }
}
