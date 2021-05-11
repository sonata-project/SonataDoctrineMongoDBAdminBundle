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

use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;

final class NumberFilter extends Filter
{
    private const CHOICES = [
        NumberOperatorType::TYPE_EQUAL => 'equals',
        NumberOperatorType::TYPE_GREATER_EQUAL => 'gte',
        NumberOperatorType::TYPE_GREATER_THAN => 'gt',
        NumberOperatorType::TYPE_LESS_EQUAL => 'lte',
        NumberOperatorType::TYPE_LESS_THAN => 'lt',
    ];

    public function getDefaultOptions(): array
    {
        return [];
    }

    public function getRenderSettings(): array
    {
        return [NumberType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || !is_numeric($data->getValue())) {
            return;
        }

        $type = $data->getType() ?? NumberOperatorType::TYPE_EQUAL;

        $operator = $this->getOperator($type);

        $query->getQueryBuilder()->field($field)->$operator((float) $data->getValue());
        $this->setActive(true);
    }

    private function getOperator(int $type): string
    {
        return self::CHOICES[$type] ?? self::CHOICES[NumberOperatorType::TYPE_EQUAL];
    }
}
