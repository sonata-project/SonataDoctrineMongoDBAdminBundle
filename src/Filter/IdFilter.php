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

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class IdFilter extends Filter
{
    public function getDefaultOptions(): array
    {
        return [
            'field_type' => TextType::class,
            'operator_type' => EqualOperatorType::class,
        ];
    }

    public function getRenderSettings(): array
    {
        return [DefaultType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'label' => $this->getLabel(),
        ]];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || null === $data->getValue()) {
            return;
        }

        $value = trim((string) $data->getValue());

        if ('' === $value) {
            return;
        }

        try {
            $objectId = new ObjectId($value);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $type = $data->getType() ?? EqualOperatorType::TYPE_EQUAL;

        if (EqualOperatorType::TYPE_EQUAL === $type) {
            $query->getQueryBuilder()->field($field)->equals($objectId);
        } else {
            $query->getQueryBuilder()->field($field)->notEqual($objectId);
        }

        $this->setActive(true);
    }
}
