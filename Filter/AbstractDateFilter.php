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
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
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
        // check data sanity
        if (empty($data['value'])) {
            return;
        }

        if ($this->range) {
            // additional data check for ranged items
            if (empty($data['value']['start']) || empty($data['value']['end'])) {
                return;
            }

            $data['value']['start'] = $data['value']['start'] instanceof \DateTime ? $data['value']['start']->getTimestamp() : 0;
            $data['value']['end'] = $data['value']['end'] instanceof \DateTime ? $data['value']['end']->getTimestamp() : 0;

            // transform types
            if ($this->getOption('input_type') !== 'timestamp') {
                $data['value']['start'] = new \MongoDate($data['value']['start']);
                $data['value']['end'] = new \MongoDate($data['value']['end']);
            }

            // default type for range filter
            $data['type'] = !isset($data['type']) || !is_numeric($data['type']) ?  DateRangeType::TYPE_BETWEEN : $data['type'];

            if ($data['type'] == DateRangeType::TYPE_NOT_BETWEEN) {
                $this->applyType($queryBuilder, $this->getOperator(DateType::TYPE_LESS_THAN), $field, $data['value']['start']);
                $this->applyType($queryBuilder, $this->getOperator(DateType::TYPE_GREATER_THAN), $field, $data['value']['end']);
            } else {
                $this->applyType($queryBuilder, $this->getOperator(DateType::TYPE_GREATER_EQUAL), $field, $data['value']['start']);
                $this->applyType($queryBuilder, $this->getOperator(DateType::TYPE_LESS_EQUAL), $field, $data['value']['end']);
            }
        } else {
            $mongoDate = null;
            if (!in_array($data['type'], array(DateType::TYPE_NOT_NULL, DateType::TYPE_NULL))) {
                $mongoDate = $data['value'] instanceof \DateTime ? $data['value']->getTimestamp() : 0;
                if ($this->getOption('input_type') !== 'timestamp') {
                    $mongoDate = new \MongoDate($mongoDate);
                }
            }

            //default type for simple filter
            $data['type'] = !isset($data['type']) || !is_numeric($data['type']) ? DateType::TYPE_EQUAL : $data['type'];
            $this->applyType($queryBuilder, $this->getOperator($data['type']), $field, $mongoDate);
        }
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
        return in_array($type, array(
                    DateType::TYPE_NULL,
                    DateType::TYPE_NOT_NULL, )
        );
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
     * Resolves DataType:: constants to MongoDb operators.
     *
     * @param int $type
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $choices = array(
            DateType::TYPE_NULL          => 'equals',
            DateType::TYPE_NOT_NULL      => 'notEqual',
            DateType::TYPE_EQUAL         => 'equals',
            DateType::TYPE_GREATER_EQUAL => 'gte',
            DateType::TYPE_GREATER_THAN  => 'gt',
            DateType::TYPE_LESS_EQUAL    => 'lte',
            DateType::TYPE_LESS_THAN     => 'lt',
        );

        return $choices[intval($type)];
    }
}
