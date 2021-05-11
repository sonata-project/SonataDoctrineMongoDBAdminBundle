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
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\AdminBundle\Form\Type\Filter\DateTimeType;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;

abstract class AbstractDateFilter extends Filter
{
    /**
     * Flag indicating that filter will have range.
     *
     * @var bool
     */
    protected $range = false;

    /**
     * Flag indicating that filter will filter by datetime instead by date.
     *
     * @var bool
     */
    protected $time = false;

    public function getDefaultOptions(): array
    {
        return ['input_type' => 'datetime'];
    }

    public function getRenderSettings(): array
    {
        $name = DateType::class;

        if ($this->time && $this->range) {
            $name = DateTimeRangeType::class;
        } elseif ($this->time) {
            $name = DateTimeType::class;
        } elseif ($this->range) {
            $name = DateRangeType::class;
        }

        return [$name, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }

    final protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || null === $data->getValue()) {
            return;
        }

        //default type for simple filter
        $type = $data->getType() ?? DateOperatorType::TYPE_EQUAL;

        switch ($type) {
            case DateOperatorType::TYPE_EQUAL:
                $this->setActive(true);

                $this->applyTypeIsEqual($query, $field, $data);

                return;

            case DateOperatorType::TYPE_GREATER_THAN:
                $this->setActive(true);

                $this->applyTypeIsGreaterThan($query, $field, $data);

                return;

            case DateOperatorType::TYPE_LESS_EQUAL:
                $this->setActive(true);

                $this->applyTypeIsLessEqual($query, $field, $data);

                return;

            case DateOperatorType::TYPE_GREATER_EQUAL:
            case DateOperatorType::TYPE_LESS_THAN:
                $this->setActive(true);

                $this->applyType($query, $this->getOperator($type), $field, $data->getValue());

                return;
        }
    }

    abstract protected function applyTypeIsLessEqual(ProxyQueryInterface $query, string $field, FilterData $data): void;

    abstract protected function applyTypeIsGreaterThan(ProxyQueryInterface $query, string $field, FilterData $data): void;

    abstract protected function applyTypeIsEqual(ProxyQueryInterface $query, string $field, FilterData $data): void;

    final protected function applyType(ProxyQueryInterface $query, string $operation, string $field, \DateTime $datetime): void
    {
        $query->getQueryBuilder()->field($field)->$operation($datetime);
        $this->setActive(true);
    }

    /**
     * Resolves DataType:: constants to MongoDb operators.
     */
    final protected function getOperator(int $type): string
    {
        $choices = [
            DateOperatorType::TYPE_EQUAL => 'equals',
            DateOperatorType::TYPE_GREATER_EQUAL => 'gte',
            DateOperatorType::TYPE_GREATER_THAN => 'gt',
            DateOperatorType::TYPE_LESS_EQUAL => 'lte',
            DateOperatorType::TYPE_LESS_THAN => 'lt',
        ];

        if (!\array_key_exists($type, $choices)) {
            throw new \InvalidArgumentException(sprintf(
                'Type "%d" is not valid, you MUST use one of the supported types: "%s".',
                $type,
                implode('", "', array_keys($choices))
            ));
        }

        return $choices[$type];
    }
}
