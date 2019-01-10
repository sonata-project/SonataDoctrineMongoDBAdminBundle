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
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\CoreBundle\Form\Type\EqualType;

class ModelFilter extends Filter
{
    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string              $alias
     * @param string              $field
     * @param mixed               $data
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !\is_array($data) || !array_key_exists('value', $data)) {
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
            'operator_type' => EqualType::class,
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
     * @param ProxyQueryInterface $queryBuilder
     * @param type                $alias
     * @param type                $field
     * @param type                $data
     *
     * @return type
     */
    protected function handleMultiple(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (0 == \count($data['value'])) {
            return;
        }

        $ids = [];
        foreach ($data['value'] as $value) {
            $ids[] = self::fixIdentifier($value->getId());
        }

        if (isset($data['type']) && EqualType::TYPE_IS_NOT_EQUAL == $data['type']) {
            $queryBuilder->field($field)->notIn($ids);
        } else {
            $queryBuilder->field($field)->in($ids);
        }

        $this->active = true;
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param type                $alias
     * @param type                $field
     * @param type                $data
     *
     * @return type
     */
    protected function handleScalar(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (empty($data['value'])) {
            return;
        }

        $id = self::fixIdentifier($data['value']->getId());

        if (isset($data['type']) && EqualType::TYPE_IS_NOT_EQUAL == $data['type']) {
            $queryBuilder->field($field)->notEqual($id);
        } else {
            $queryBuilder->field($field)->equals($id);
        }

        $this->active = true;
    }

    /**
     * Return \MongoId if $id is MongoId in string representation, otherwise custom string.
     *
     * @param mixed $id
     *
     * @return \MongoId|string
     */
    protected static function fixIdentifier($id)
    {
        try {
            return new \MongoId($id);
        } catch (\MongoException $ex) {
            return $id;
        }
    }

    /**
     * Identifier field name is 'field' if mapping type is simple; otherwise, it's 'field.$id'.
     *
     * @param string $field
     *
     * @return string
     */
    protected function getIdentifierField($field)
    {
        $field_mapping = $this->getFieldMapping();

        return (true === $field_mapping['simple']) ? $field : $field.'.$id';
    }
}
