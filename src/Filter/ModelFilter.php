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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class ModelFilter extends Filter
{
    /**
     * NEXT_MAJOR: Remove $alias parameter.
     *
     * @return void
     */
    public function filter(ProxyQueryInterface $query, $alias, $field, $data)
    {
        if (!$data || !\is_array($data) || !\array_key_exists('value', $data)) {
            return;
        }

        if ($data['value'] instanceof Collection) {
            $data['value'] = $data['value']->toArray();
        }

        $field = $this->getIdentifierField($field);

        if (\is_array($data['value'])) {
            // NEXT_MAJOR: Remove $alias argument.
            $this->handleMultiple($query, $alias, $field, $data);
        } else {
            // NEXT_MAJOR: Remove $alias argument.
            $this->handleScalar($query, $alias, $field, $data);
        }
    }

    public function getDefaultOptions()
    {
        return [
            'mapping_type' => false,
            // NEXT_MAJOR: Remove this line.
            'field_name' => false,
            'field_type' => DocumentType::class,
            'field_options' => [],
            'operator_type' => EqualOperatorType::class,
            'operator_options' => [],
        ];
    }

    public function getRenderSettings()
    {
        return [DefaultType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label' => $this->getLabel(),
        ]];
    }

    /**
     * NEXT_MAJOR: Remove $alias parameter.
     *
     * @param string $alias
     * @param string $field
     * @param array  $data
     *
     * @return void
     */
    protected function handleMultiple(ProxyQueryInterface $query, $alias, $field, $data)
    {
        if (0 === \count($data['value'])) {
            return;
        }

        $ids = [];
        foreach ($data['value'] as $value) {
            $ids[] = self::fixIdentifier($value->getId());
        }

        if (isset($data['type']) && EqualOperatorType::TYPE_NOT_EQUAL === $data['type']) {
            $query->field($field)->notIn($ids);
        } else {
            $query->field($field)->in($ids);
        }

        $this->active = true;
    }

    /**
     * NEXT_MAJOR: Remove $alias parameter.
     *
     * @param string $alias
     * @param string $field
     * @param array  $data
     *
     * @return void
     */
    protected function handleScalar(ProxyQueryInterface $query, $alias, $field, $data)
    {
        if (empty($data['value'])) {
            return;
        }

        $id = self::fixIdentifier($data['value']->getId());

        if (isset($data['type']) && EqualOperatorType::TYPE_NOT_EQUAL === $data['type']) {
            $query->field($field)->notEqual($id);
        } else {
            $query->field($field)->equals($id);
        }

        $this->active = true;
    }

    /**
     * Return ObjectId if $id is ObjectId in string representation, otherwise custom string.
     *
     * @param mixed $id
     *
     * @return string|ObjectId
     */
    protected static function fixIdentifier($id)
    {
        try {
            return new ObjectId($id);
        } catch (InvalidArgumentException $ex) {
            return $id;
        }
    }

    /**
     * Get identifier field name based on mapping type.
     *
     * @param string $field
     *
     * @return string
     */
    protected function getIdentifierField($field)
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
