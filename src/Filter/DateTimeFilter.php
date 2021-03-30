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

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

final class DateTimeFilter extends AbstractDateFilter
{
    /**
     * Flag indicating that filter will filter by datetime instead by date.
     *
     * @var bool
     */
    protected $time = true;

    public function getFieldType(): string
    {
        return $this->getOption('field_type', DateTimeType::class);
    }

    /**
     * @param array $data
     */
    protected function applyTypeIsLessEqual(BaseProxyQueryInterface $query, string $field, $data): void
    {
        // Add a minute so less then equal selects all seconds.
        $data['value']->add(new \DateInterval('PT1M'));

        $this->applyType($query, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * @param array $data
     */
    protected function applyTypeIsGreaterThan(BaseProxyQueryInterface $query, string $field, $data): void
    {
        // Add 59 seconds so anything above the minute is selected
        $data['value']->add(new \DateInterval('PT59S'));

        $this->applyType($query, $this->getOperator($data['type']), $field, $data['value']);
    }

    /**
     * Because we lack a second variable we select a range covering the entire minute.
     *
     * @param array $data
     */
    protected function applyTypeIsEqual(BaseProxyQueryInterface $query, string $field, $data): void
    {
        /* NEXT_MAJOR: Remove this deprecation and update the typehint */
        if (!$query instanceof ProxyQueryInterface) {
            @trigger_error(sprintf(
                'Passing %s as argument 1 to %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x'
                .' and will throw a \TypeError error in version 4.0. You MUST pass an instance of %s instead.',
                \get_class($query),
                __METHOD__,
                ProxyQueryInterface::class
            ), \E_USER_DEPRECATED);
        }

        /** @var \DateTime $end */
        $end = clone $data['value'];
        $end->add(new \DateInterval('PT1M'));

        $query->getQueryBuilder()->field($field)->range($data['value'], $end);
    }
}
