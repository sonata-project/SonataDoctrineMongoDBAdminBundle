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

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;

final class ModelFilter extends Filter
{
    public function getDefaultOptions(): array
    {
        return [
            'mapping_type' => false,
            'field_type' => DocumentType::class,
            'field_options' => [],
            'operator_type' => EqualOperatorType::class,
            'operator_options' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label' => $this->getLabel(),
        ];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue()) {
            return;
        }

        $value = $data->getValue();

        if ($value instanceof Collection) {
            $data = $data->changeValue($value->toArray());
        }

        $field = $this->getIdentifierField($field);

        if (\is_array($data->getValue())) {
            $this->handleMultiple($query, $field, $data);
        } else {
            $this->handleScalar($query, $field, $data);
        }
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    protected function handleMultiple(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (0 === \count($data->getValue())) {
            return;
        }

        $ids = [];
        foreach ($data->getValue() as $value) {
            $ids[] = self::fixIdentifier($value->getId());
        }

        if ($data->isType(EqualOperatorType::TYPE_NOT_EQUAL)) {
            $query->getQueryBuilder()->field($field)->notIn($ids);
        } else {
            $query->getQueryBuilder()->field($field)->in($ids);
        }

        $this->setActive(true);
    }

    /**
     * @param ProxyQueryInterface<object> $query
     */
    protected function handleScalar(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        $id = self::fixIdentifier($data->getValue()->getId());

        if ($data->isType(EqualOperatorType::TYPE_NOT_EQUAL)) {
            $query->getQueryBuilder()->field($field)->notEqual($id);
        } else {
            $query->getQueryBuilder()->field($field)->equals($id);
        }

        $this->setActive(true);
    }

    /**
     * Return ObjectId if $id is ObjectId in string representation, otherwise custom string.
     */
    protected static function fixIdentifier(mixed $id): string|ObjectId
    {
        try {
            return new ObjectId($id);
        } catch (InvalidArgumentException) {
            return $id;
        }
    }

    /**
     * Get identifier field name based on mapping type.
     */
    private function getIdentifierField(string $field): string
    {
        $field_mapping = $this->getFieldMapping();

        if (isset($field_mapping['storeAs'])) {
            switch ($field_mapping['storeAs']) {
                case ClassMetadata::REFERENCE_STORE_AS_REF:
                    return $field.'.id';
                case ClassMetadata::REFERENCE_STORE_AS_ID:
                    return $field;
                case ClassMetadata::REFERENCE_STORE_AS_DB_REF_WITH_DB:
                case ClassMetadata::REFERENCE_STORE_AS_DB_REF:
                    return $field.'.$id';
            }
        }

        return $field.'._id';
    }
}
