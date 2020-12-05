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
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class ChoiceFilter extends Filter
{
    /**
     * NEXT_MAJOR: Remove $alias parameter.
     *
     * @return void
     */
    public function filter(ProxyQueryInterface $query, $alias, $field, $data)
    {
        if (!$data || !\is_array($data) || !\array_key_exists('type', $data) || !\array_key_exists('value', $data)) {
            return;
        }

        if (\is_array($data['value'])) {
            if (0 === \count($data['value'])) {
                return;
            }

            if (\in_array('all', $data['value'], true)) {
                return;
            }

            if (ContainsOperatorType::TYPE_NOT_CONTAINS === $data['type']) {
                $query->field($field)->notIn($data['value']);
            } else {
                $query->field($field)->in($data['value']);
            }

            $this->active = true;
        } else {
            if ('' === $data['value'] || null === $data['value'] || false === $data['value'] || 'all' === $data['value']) {
                return;
            }

            if (ContainsOperatorType::TYPE_NOT_CONTAINS === $data['type']) {
                $query->field($field)->notEqual($data['value']);
            } else {
                $query->field($field)->equals($data['value']);
            }

            $this->active = true;
        }
    }

    public function getDefaultOptions()
    {
        return [];
    }

    public function getRenderSettings()
    {
        return [DefaultType::class, [
            'operator_type' => ChoiceType::class,
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
