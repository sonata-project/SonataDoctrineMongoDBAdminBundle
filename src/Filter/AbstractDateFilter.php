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
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;

abstract class AbstractDateFilter extends Filter
{
    /**
     * Flag indicating that filter will have range.
     *
     * @var bool
     */
    protected $range = false;

    /**
     * Flag indicating that filter will filter by datetime instead by date.
     *
     * @var bool
     */
    protected $time = false;

    /**
     * NEXT_MAJOR: Remove $alias parameter.
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
        //check data sanity
        if (true !== \is_array($value)) {
            return;
        }

        //default type for simple filter
        $value['type'] = !isset($value['type']) || !is_numeric($value['type']) ? DateOperatorType::TYPE_EQUAL : $value['type'];

        // Some types do not require a value to be set (NULL, NOT NULL).
        if (!isset($value['value']) && $this->typeDoesRequireValue($value['type'])) {
            return;
        }

        switch ($value['type']) {
            case DateOperatorType::TYPE_EQUAL:
                $this->active = true;

                $this->applyTypeIsEqual($queryBuilder, $field, $value);

                return;

            case DateOperatorType::TYPE_GREATER_THAN:
                $this->active = true;

                $this->applyTypeIsGreaterThan($queryBuilder, $field, $value);

                return;

            case DateOperatorType::TYPE_LESS_EQUAL:
                $this->active = true;

                $this->applyTypeIsLessEqual($queryBuilder, $field, $value);

                return;

            case DateOperatorType::TYPE_NULL:
            case DateOperatorType::TYPE_NOT_NULL:
                $this->active = true;

                $this->applyType($queryBuilder, $this->getOperator($value['type']), $field, null);

                return;

            case DateOperatorType::TYPE_GREATER_EQUAL:
            case DateOperatorType::TYPE_LESS_THAN:
                $this->active = true;

                $this->applyType($queryBuilder, $this->getOperator($value['type']), $field, $value['value']);

                return;
        }
    }

    public function getDefaultOptions()
    {
        return ['input_type' => 'datetime'];
    }

    public function getRenderSettings()
    {
        $name = DateType::class;

        if ($this->time && $this->range) {
            $name = DateTimeRangeType::class;
        } elseif ($this->time) {
            $name = DateTimeType::class;
        } elseif ($this->range) {
            $name = DateRangeType::class;
        }

        return [$name, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }

    abstract protected function applyTypeIsLessEqual(ProxyQueryInterface $queryBuilder, string $field, array $data);

    abstract protected function applyTypeIsGreaterThan(ProxyQueryInterface $queryBuilder, string $field, array $data);

    abstract protected function applyTypeIsEqual(ProxyQueryInterface $queryBuilder, string $field, array $data);

    /**
     * @param string    $operation
     * @param string    $field
     * @param \DateTime $datetime
     */
    protected function applyType(ProxyQueryInterface $queryBuilder, $operation, $field, ?\DateTime $datetime = null)
    {
        $queryBuilder->field($field)->$operation($datetime);
        $this->active = true;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Returns if the filter type requires a value to be set.
     *
     * @param int $type
     *
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4, to be removed in 4.0.'.
     *
     * @return bool
     */
    protected function typeRequiresValue($type)
    {
        @trigger_error(sprintf(
            '"%s()" is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.4 and will be removed in version 4.0.',
            __METHOD__
        ), E_USER_DEPRECATED);

        return \in_array($type, [
            DateOperatorType::TYPE_NULL,
            DateOperatorType::TYPE_NOT_NULL,
        ], true);
    }

    /**
     * Resolves DataType:: constants to MongoDb operators.
     *
     * @param int $type
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $choices = [
            DateOperatorType::TYPE_NULL => 'equals',
            DateOperatorType::TYPE_NOT_NULL => 'notEqual',
            DateOperatorType::TYPE_EQUAL => 'equals',
            DateOperatorType::TYPE_GREATER_EQUAL => 'gte',
            DateOperatorType::TYPE_GREATER_THAN => 'gt',
            DateOperatorType::TYPE_LESS_EQUAL => 'lte',
            DateOperatorType::TYPE_LESS_THAN => 'lt',
        ];

        return $choices[(int) $type];
    }

    private function typeDoesRequireValue(int $type): bool
    {
        return !\in_array($type, [
            DateOperatorType::TYPE_NULL,
            DateOperatorType::TYPE_NOT_NULL,
        ], true);
    }
}
