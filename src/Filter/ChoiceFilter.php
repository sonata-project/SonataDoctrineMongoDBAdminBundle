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
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
        if (!$value || !\is_array($value) || !\array_key_exists('type', $value) || !\array_key_exists('value', $value)) {
            return;
        }

        if (\is_array($value['value'])) {
            if (0 === \count($value['value'])) {
                return;
            }

            if (\in_array('all', $value['value'], true)) {
                return;
            }

            if (ContainsOperatorType::TYPE_NOT_CONTAINS === $value['type']) {
                $queryBuilder->field($field)->notIn($value['value']);
            } else {
                $queryBuilder->field($field)->in($value['value']);
            }

            $this->active = true;
        } else {
            if ('' === $value['value'] || null === $value['value'] || false === $value['value'] || 'all' === $value['value']) {
                return;
            }

            if (ContainsOperatorType::TYPE_NOT_CONTAINS === $value['type']) {
                $queryBuilder->field($field)->notEqual($value['value']);
            } else {
                $queryBuilder->field($field)->equals($value['value']);
            }

            $this->active = true;
        }
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
        return [DefaultType::class, [
            'operator_type' => ChoiceType::class,
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
