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

class StringFilter extends Filter
{
    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string              $alias
     * @param string              $field
     * @param string              $data
     */
    public function filter(ProxyQueryInterface $queryBuilder, $name, $field, $data): void
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);

        if (0 == strlen($data['value'])) {
            return;
        }

        $data['type'] = isset($data['type']) && !empty($data['type']) ? $data['type'] : ChoiceType::TYPE_CONTAINS;

        $obj = $queryBuilder;
        if (self::CONDITION_OR == $this->condition) {
            $obj = $queryBuilder->expr();
        }

        if (ChoiceType::TYPE_EQUAL == $data['type']) {
            $obj->field($field)->equals($data['value']);
        } elseif (ChoiceType::TYPE_CONTAINS == $data['type']) {
            $obj->field($field)->equals(new \MongoRegex(sprintf('/%s/i', $data['value'])));
        } elseif (ChoiceType::TYPE_NOT_CONTAINS == $data['type']) {
            $obj->field($field)->not(new \MongoRegex(sprintf('/%s/i', $data['value'])));
        }

        if (self::CONDITION_OR == $this->condition) {
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
