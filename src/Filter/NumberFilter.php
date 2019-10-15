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
use Sonata\AdminBundle\Form\Type\Filter\NumberType;

class NumberFilter extends Filter
{
    /**
     * @param string $alias
     * @param string $field
     * @param string $data
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !\is_array($data) || !\array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $type = $data['type'] ?? false;

        $operator = $this->getOperator($type);

        if (!$operator) {
            $operator = 'equals';
        }

        $queryBuilder->field($field)->$operator((float) $data['value']);
        $this->active = true;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [];
    }

    public function getRenderSettings()
    {
        return [NumberType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }

    /**
     * @param $type
     *
     * @return bool
     */
    private function getOperator($type)
    {
        $choices = [
            NumberType::TYPE_EQUAL => 'equals',
            NumberType::TYPE_GREATER_EQUAL => 'gte',
            NumberType::TYPE_GREATER_THAN => 'gt',
            NumberType::TYPE_LESS_EQUAL => 'lte',
            NumberType::TYPE_LESS_THAN => 'lt',
        ];

        return $choices[$type] ?? false;
    }
}
