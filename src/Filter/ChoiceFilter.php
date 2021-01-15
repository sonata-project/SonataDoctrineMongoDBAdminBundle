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
    public function filter(ProxyQueryInterface $query, string $field, $data): void
    {
        if (!$data || !\is_array($data) || !\array_key_exists('type', $data) || !\array_key_exists('value', $data)) {
            return;
        }

        if (\is_array($data['value'])) {
            if (0 === \count($data['value'])) {
                return;
            }

            if (ContainsOperatorType::TYPE_NOT_CONTAINS === $data['type']) {
                $query->field($field)->notIn($data['value']);
            } else {
                $query->field($field)->in($data['value']);
            }

            $this->active = true;
        } else {
            if ('' === $data['value'] || null === $data['value'] || false === $data['value']) {
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

    public function getDefaultOptions(): array
    {
        return [];
    }

    public function getRenderSettings(): array
    {
        return [DefaultType::class, [
            'operator_type' => ChoiceType::class,
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
