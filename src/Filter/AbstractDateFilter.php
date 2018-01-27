<?php

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
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        //check data sanity
        if (true !== is_array($data)) {
            return;
        }

        //default type for simple filter
        $data['type'] = !isset($data['type']) || !is_numeric($data['type']) ? DateType::TYPE_EQUAL : $data['type'];

        // Some types do not require a value to be set (NULL, NOT NULL).
        if (!$this->typeRequiresValue($data['type']) && !$data['value']) {
            return;
        }

        switch ($data['type']) {
            case DateType::TYPE_EQUAL:
                return $this->applyTypeIsEqual($queryBuilder, $field, $data);

            case DateType::TYPE_GREATER_THAN:
                if (!array_key_exists('value', $data) || !$data['value']) {
                    return;
                }

                return $this->applyTypeIsGreaterThan($queryBuilder, $field, $data);

            case DateType::TYPE_LESS_EQUAL:
                if (!array_key_exists('value', $data) || !$data['value']) {
                    return;
                }

                return $this->applyTypeIsLessEqual($queryBuilder, $field, $data);

            case DateType::TYPE_NULL:
            case DateType::TYPE_NOT_NULL:
                return $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, null);

            case DateType::TYPE_GREATER_EQUAL:
            case DateType::TYPE_LESS_THAN:
                return $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, $data['value']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return ['input_type' => 'datetime'];
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string                                           $operation
     * @param string                                           $field
     * @param \DateTime                                        $datetime
     */
    protected function applyType(ProxyQueryInterface $queryBuilder, $operation, $field, \DateTime $datetime = null)
    {
        $queryBuilder->field($field)->$operation($datetime);
        $this->active = true;
    }

    /**
     * Returns if the filter type requires a value to be set.
     *
     * @param int $type
     *
     * @return bool
     */
    protected function typeRequiresValue($type)
    {
        return in_array($type, [
            DateType::TYPE_NULL,
            DateType::TYPE_NOT_NULL,
        ]);
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
            DateType::TYPE_NULL => 'equals',
            DateType::TYPE_NOT_NULL => 'notEqual',
            DateType::TYPE_EQUAL => 'equals',
            DateType::TYPE_GREATER_EQUAL => 'gte',
            DateType::TYPE_GREATER_THAN => 'gt',
            DateType::TYPE_LESS_EQUAL => 'lte',
            DateType::TYPE_LESS_THAN => 'lt',
        ];

        return $choices[(int) $type];
    }
}
