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

use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;

class ChoiceFilter extends Filter
{

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $field
     * @param mixed $data
     * @return
     */
    public function filter($queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        if (is_array($data['value'])) {
            if (count($data['value']) == 0) {
                return;
            }

            if (in_array('all', $data['value'])) {
                return;
            }

            if ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                $queryBuilder->field($field)->notIn($data['value']);
            } else {
                $queryBuilder->field($field)->in($data['value']);
            }
        } else {

            if (empty($data['value']) || $data['value'] == 'all') {
                return;
            }

            if ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                $queryBuilder->field($field)->notEqual($data['value']);
            } else {
                $queryBuilder->field($field)->equals($data['value']);
            }
        }
    }

    /**
     * @param $type
     * @return bool
     */
    private function getOperator($type)
    {
        $choices = array(
            ChoiceType::TYPE_CONTAINS => 'IN',
            ChoiceType::TYPE_NOT_CONTAINS => 'NOT IN',
            ChoiceType::TYPE_EQUAL => '=',
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
        return array('sonata_type_filter_default', array(
                'operator_type' => 'sonata_type_boolean',
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel()
        ));
    }
}
