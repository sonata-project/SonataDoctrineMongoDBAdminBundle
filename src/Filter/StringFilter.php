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
    /**
     * @param string $field
     * @param array  $data
     */
    public function filter(ProxyQueryInterface $queryBuilder, $name, $field, $data)
    {
        if (!$data || !\is_array($data) || !\array_key_exists('value', $data) || null === $data['value']) {
            return;
        }

        $data['value'] = trim($data['value']);

        if ('' === $data['value']) {
            return;
        }

        $data['type'] = isset($data['type']) && !empty($data['type']) ? $data['type'] : ContainsOperatorType::TYPE_CONTAINS;

        $obj = $queryBuilder;
        if (self::CONDITION_OR === $this->condition) {
            $obj = $queryBuilder->expr();
        }

        if (ContainsOperatorType::TYPE_EQUAL === $data['type']) {
            $obj->field($field)->equals($data['value']);
        } elseif (ContainsOperatorType::TYPE_CONTAINS === $data['type']) {
            $obj->field($field)->equals(new Regex($data['value'], 'i'));
        } elseif (ContainsOperatorType::TYPE_NOT_CONTAINS === $data['type']) {
            $obj->field($field)->not(new Regex($data['value'], 'i'));
        }

        if (self::CONDITION_OR === $this->condition) {
            $queryBuilder->addOr($obj);
        }

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
        return [ChoiceType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
