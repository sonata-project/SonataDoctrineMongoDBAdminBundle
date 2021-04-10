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

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;

final class DateFilter extends AbstractDateFilter
{
    public function getFieldType(): string
    {
        return $this->getOption('field_type', DateType::class);
    }

    protected function applyTypeIsLessEqual(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        $data->getValue()->add(new \DateInterval('P1D'));

        $this->applyType($query, $this->getOperator($data->getType()), $field, $data->getValue());
    }

    protected function applyTypeIsGreaterThan(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        $data->getValue()->add(new \DateInterval('P1D'));

        $this->applyType($query, $this->getOperator($data->getType()), $field, $data->getValue());
    }

    /**
     * Because we lack a time variable we select a range from the days start to end.
     *
     * @author Wesley van Opdorp <wesley.van.opdorp@freshheads.com>
     */
    protected function applyTypeIsEqual(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        $end = clone $data->getValue();
        $end->add(new \DateInterval('P1D'));

        $query->getQueryBuilder()->field($field)->range($data->getValue(), $end);
    }
}
