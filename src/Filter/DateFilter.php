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

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class DateFilter extends AbstractDateFilter
{
    public function getFieldType(): string
    {
        return $this->getOption('field_type', DateType::class);
    }

    /**
     * @param string $field
     * @param array  $data
     */
    protected function applyTypeIsLessEqual(ProxyQueryInterface $query, $field, $data)
    {
        $data['value']->add(new \DateInterval('P1D'));

        $this->applyType($query, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * @param string $field
     * @param array  $data
     */
    protected function applyTypeIsGreaterThan(ProxyQueryInterface $query, $field, $data)
    {
        $data['value']->add(new \DateInterval('P1D'));

        $this->applyType($query, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * Because we lack a time variable we select a range from the days start to end.
     *
     * @author Wesley van Opdorp <wesley.van.opdorp@freshheads.com>
     *
     * @param string $field
     * @param array  $data
     */
    protected function applyTypeIsEqual(ProxyQueryInterface $query, $field, $data)
    {
        $end = clone $data['value'];
        $end->add(new \DateInterval('P1D'));

        $query->field($field)->range($data['value'], $end);
    }
}
