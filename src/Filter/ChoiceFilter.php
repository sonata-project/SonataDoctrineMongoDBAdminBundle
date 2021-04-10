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
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;

final class ChoiceFilter extends Filter
{
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

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $queryBuilder = $query->getQueryBuilder();

        $value = $data->getValue();

        if (\is_array($value)) {
            if (0 === \count($value)) {
                return;
            }

            if ($data->isType(ContainsOperatorType::TYPE_NOT_CONTAINS)) {
                $queryBuilder->field($field)->notIn($value);
            } else {
                $queryBuilder->field($field)->in($value);
            }

            $this->active = true;
        } else {
            if ('' === $value || null === $value || false === $value) {
                return;
            }

            if ($data->isType(ContainsOperatorType::TYPE_NOT_CONTAINS)) {
                $queryBuilder->field($field)->notEqual($value);
            } else {
                $queryBuilder->field($field)->equals($value);
            }

            $this->active = true;
        }
    }
}
