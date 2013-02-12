<?php

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

abstract class AbstractDateFilter extends Filter
{
    /**
     * Flag indicating that filter will have range
     * @var boolean
     */
    protected $range = false;

    /**
     * Flag indicating that filter will filter by datetime instead by date
     * @var boolean
     */
    protected $time = false;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {

        //check data sanity
        if (is_array($data) !== true) {
            return;
        }

        //default type for simple filter
        $data['type'] = !isset($data['type']) || !is_numeric($data['type']) ? DateType::TYPE_EQUAL : $data['type'];

        // Some types do not require a value to be set (NULL, NOT NULL).
        if (!$this->typeRequiresValue($data['type']) && !$data['value']) {
            return;
        }

        switch($data['type'])
        {
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
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryBuilder
     * @param string $operation
     * @param string $field
     * @param \DateTime $datetime
     */
    protected function applyType(ProxyQueryInterface $queryBuilder, $operation, $field, \DateTime $datetime = null)
    {
        $queryBuilder->field($field)->$operation($datetime);
    }

    /**
     * Returns if the filter type requires a value to be set.
     *
     * @param integer $type
     * @return bool
     */
    protected function typeRequiresValue($type)
    {
        return (in_array($type, array(
            DateType::TYPE_NULL,
            DateType::TYPE_NOT_NULL)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array('input_type' => 'datetime');
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $name = 'sonata_type_filter_date';

        if ($this->time) {
            $name .= 'time';
        }

        if ($this->range) {
            $name .= '_range';
        }

        return array($name, array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel(),
        ));
    }

    /**
     * Resolves DataType:: constants to MongoDb operators
     *
     * @param integer $type
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $choices = array(
            DateType::TYPE_NULL             => 'equals',
            DateType::TYPE_NOT_NULL         => 'notEqual',
            DateType::TYPE_EQUAL            => 'equals',
            DateType::TYPE_GREATER_EQUAL    => 'gte',
            DateType::TYPE_GREATER_THAN     => 'gt',
            DateType::TYPE_LESS_EQUAL       => 'lte',
            DateType::TYPE_LESS_THAN        => 'lt'
        );

        return $choices[ intval($type) ];
    }
}