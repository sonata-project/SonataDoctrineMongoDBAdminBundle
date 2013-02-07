<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class NumberFilter extends Filter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param string $data
     * @return
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $type = isset($data['type']) ? $data['type'] : false;

        $operator = $this->getOperator($type);

        if (!$operator) {
            $operator = 'equals';
        }

        $queryBuilder->field($field)->$operator((float) $data['value']);
    }

    /**
     * @param $type
     * @return bool
     */
    private function getOperator($type)
    {
        $choices = array(
            NumberType::TYPE_EQUAL         => 'equals',
            NumberType::TYPE_GREATER_EQUAL => 'gte',
            NumberType::TYPE_GREATER_THAN  => 'gt',
            NumberType::TYPE_LESS_EQUAL    => 'lte',
            NumberType::TYPE_LESS_THAN     => 'lt',
        );

        return isset($choices[$type]) ? $choices[$type] : false;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }

    public function getRenderSettings()
    {
        return array('sonata_type_filter_number', array(
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel()
        ));
    }
}
