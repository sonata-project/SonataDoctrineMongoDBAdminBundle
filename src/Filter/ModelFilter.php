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
use MongoDB\Exception\InvalidArgumentException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;

class ModelFilter extends Filter
{
    /**
     * @param string $alias
     * @param string $field
     * @param mixed  $data
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !\is_array($data) || !\array_key_exists('value', $data)) {
            return;
        }

        if ($data['value'] instanceof Collection) {
            $data['value'] = $data['value']->toArray();
        }

        $field = $this->getIdentifierField($field);

        if (\is_array($data['value'])) {
            $this->handleMultiple($queryBuilder, $alias, $field, $data);
        } else {
            $this->handleScalar($queryBuilder, $alias, $field, $data);
        }
    }

    public function getDefaultOptions()
    {
        return [
            'mapping_type' => false,
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
     * @param string $alias
     * @param string $field
     * @param array  $data
     *
     * @return void
     */
    protected function handleMultiple(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (0 === \count($data['value'])) {
            return;
        }

        $ids = [];
        foreach ($data['value'] as $value) {
            $ids[] = self::fixIdentifier($value->getId());
        }

        if (isset($data['type']) && EqualOperatorType::TYPE_NOT_EQUAL === $data['type']) {
            $queryBuilder->field($field)->notIn($ids);
        } else {
            $queryBuilder->field($field)->in($ids);
        }

        $this->active = true;
    }

    /**
     * @param string $alias
     * @param string $field
     * @param array  $data
     *
     * @return void
     */
    protected function handleScalar(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (empty($data['value'])) {
            return;
        }

        $id = self::fixIdentifier($data['value']->getId());

        if (isset($data['type']) && EqualOperatorType::TYPE_NOT_EQUAL === $data['type']) {
            $queryBuilder->field($field)->notEqual($id);
        } else {
            $queryBuilder->field($field)->equals($id);
        }

        $this->active = true;
    }

    /**
     * Return \MongoId|ObjectId if $id is MongoId|ObjectId in string representation, otherwise custom string.
     *
     * @param mixed $id
     *
     * @return \MongoId|string|ObjectId
     */
    protected static function fixIdentifier($id)
    {
        // NEXT_MAJOR: Use only ObjectId when dropping support for doctrine/mongodb-odm 1.x
        if (class_exists(ObjectId::class)) {
            try {
                return new ObjectId($id);
            } catch (InvalidArgumentException $ex) {
                return $id;
            }
        }

        try {
            return new \MongoId($id);
        } catch (\MongoException $ex) {
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
