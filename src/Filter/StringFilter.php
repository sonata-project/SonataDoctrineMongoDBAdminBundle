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
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;

final class StringFilter extends Filter
{
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

    protected function filter(ProxyQueryInterface $query, string $field, array $data): void
    {
        if (!isset($data['value'])) {
            return;
        }

        $data['value'] = trim($data['value']);

        if ('' === $data['value']) {
            return;
        }

        $data['type'] = isset($data['type']) && !empty($data['type']) ? $data['type'] : ContainsOperatorType::TYPE_CONTAINS;

        $obj = $query->getQueryBuilder();
        if (self::CONDITION_OR === $this->condition) {
            $obj = $query->getQueryBuilder()->expr();
        }

        if (ContainsOperatorType::TYPE_EQUAL === $data['type']) {
            $obj->field($field)->equals($data['value']);
        } elseif (ContainsOperatorType::TYPE_CONTAINS === $data['type']) {
            $obj->field($field)->equals(new Regex($data['value'], 'i'));
        } elseif (ContainsOperatorType::TYPE_NOT_CONTAINS === $data['type']) {
            $obj->field($field)->not(new Regex($data['value'], 'i'));
        }

        if (self::CONDITION_OR === $this->condition) {
            $query->getQueryBuilder()->addOr($obj);
        }

        $this->active = true;
    }
}
