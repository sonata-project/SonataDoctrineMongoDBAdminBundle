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
use Sonata\AdminBundle\Form\Type\Operator\DateRangeOperatorType;
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
        return [
            'field_type' => $this->getDateFieldType(),
        ];
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

    /**
     * @phpstan-return class-string
     */
    abstract protected function getDateFieldType(): string;

    final protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || null === $data->getValue()) {
            return;
        }

        if ($this->range) {
            $this->filterRange($query, $field, $data);

            return;
        }

        $value = $data->getValue();

        if (!$value instanceof \DateTimeInterface) {
            return;
        }

        \assert($value instanceof \DateTime || $value instanceof \DateTimeImmutable);

        //default type for simple filter
        $type = $data->getType() ?? DateOperatorType::TYPE_EQUAL;

        // date filter should filter records for the whole day
        if (false === $this->time && DateOperatorType::TYPE_EQUAL === $type) {
            $endValue = clone $value;
            $endValue = $endValue->add(new \DateInterval('P1D'));

            $this->applyType($query, $this->getOperator(DateOperatorType::TYPE_GREATER_EQUAL), $field, $value);
            $this->applyType($query, $this->getOperator(DateOperatorType::TYPE_LESS_THAN), $field, $endValue);

            return;
        }

        $this->applyType($query, $this->getOperator($type), $field, $value);
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

    private function applyType(ProxyQueryInterface $queryBuilder, string $operation, string $field, \DateTimeInterface $value): void
    {
        $queryBuilder->getQueryBuilder()->field($field)->$operation($value);
        $this->setActive(true);
    }

    private function filterRange(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        $value = $data->getValue();

        // additional data check for ranged items
        if (
            !\is_array($value)
            || !\array_key_exists('start', $value)
            || !\array_key_exists('end', $value)
        ) {
            return;
        }

        if (
            !$value['start'] instanceof \DateTimeInterface
            && !$value['end'] instanceof \DateTimeInterface
        ) {
            return;
        }

        // date filter should filter records for the whole days
        if (
            false === $this->time
            && ($value['end'] instanceof \DateTime || $value['end'] instanceof \DateTimeImmutable)
        ) {
            // since the received `\DateTime` object  uses the model timezone to represent
            // the value submitted by the view (which can use a different timezone) and this
            // value is intended to contain a time in the beginning of a date (IE, if the model
            // object is configured to use UTC timezone, the view object "2020-11-07 00:00:00.0-03:00"
            // is transformed to "2020-11-07 03:00:00.0+00:00" in the model object), we increment
            // the time part by adding "23:59:59" in order to cover the whole end date and get proper
            // results from queries like "o.created_at <= :date_end".
            $value['end'] = $value['end']->modify('+23 hours 59 minutes 59 seconds');
        }

        // default type for range filter
        $type = $data->getType() ?? DateRangeOperatorType::TYPE_BETWEEN;

        if (DateRangeOperatorType::TYPE_NOT_BETWEEN === $type) {
            $this->applyType($query, $this->getOperator(DateOperatorType::TYPE_LESS_THAN), $field, $value['start']);
            $this->applyType($query, $this->getOperator(DateOperatorType::TYPE_GREATER_THAN), $field, $value['end']);
        } else {
            $this->applyType($query, $this->getOperator(DateOperatorType::TYPE_GREATER_EQUAL), $field, $value['start']);
            $this->applyType($query, $this->getOperator(DateOperatorType::TYPE_LESS_EQUAL), $field, $value['end']);
        }
    }
}
