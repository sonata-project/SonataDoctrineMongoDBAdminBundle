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

use MongoDB\BSON\Regex;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class StringFilter extends Filter
{
    public function filter(ProxyQueryInterface $queryBuilder, string $field, $value): void
    {
        if (!$value || !\is_array($value) || !\array_key_exists('value', $value) || null === $value['value']) {
            return;
        }

        $value['value'] = trim($value['value']);

        if ('' === $value['value']) {
            return;
        }

        $value['type'] = isset($value['type']) && !empty($value['type']) ? $value['type'] : ContainsOperatorType::TYPE_CONTAINS;

        $obj = $queryBuilder;
        if (self::CONDITION_OR === $this->condition) {
            $obj = $queryBuilder->expr();
        }

        if (ContainsOperatorType::TYPE_EQUAL === $value['type']) {
            $obj->field($field)->equals($value['value']);
        } elseif (ContainsOperatorType::TYPE_CONTAINS === $value['type']) {
            $obj->field($field)->equals(new Regex($value['value'], 'i'));
        } elseif (ContainsOperatorType::TYPE_NOT_CONTAINS === $value['type']) {
            $obj->field($field)->not(new Regex($value['value'], 'i'));
        }

        if (self::CONDITION_OR === $this->condition) {
            $queryBuilder->addOr($obj);
        }

        $this->active = true;
    }

    public function getDefaultOptions(): array
    {
        return [];
    }

    public function getRenderSettings(): array
    {
        return [ChoiceType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
