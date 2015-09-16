<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <josluis.lopes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\CoreBundle\Form\Type\EqualType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\Common\Collections\Collection;

class ModelAutocompleteFilter extends Filter
{

    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        if ($data['value'] instanceof Collection) {
            $data['value'] = $data['value']->toArray();
        }

        $field = $this->getIdentifierField($field);

        if (is_array($data['value'])) {
            $this->handleMultiple($queryBuilder, $alias, $field, $data);
        } else {
            $this->handleScalar($queryBuilder, $alias, $field, $data);
        }

    }

    /**
     *
     * @param  ProxyQueryInterface $queryBuilder
     * @param  type                $alias
     * @param  type                $field
     * @param  type                $data
     * @return type
     */
    protected function handleMultiple(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (count($data['value']) == 0) {
            return;
        }

        $ids = array();
        foreach ($data['value'] as $value) {
            $ids[] = self::fixIdentifier($value->getId());
        }

        if (isset($data['type']) && $data['type'] == EqualType::TYPE_IS_NOT_EQUAL) {
            $queryBuilder->field($field)->notIn($ids);
        } else {
            $queryBuilder->field($field)->in($ids);
        }

        $this->active = true;
    }

    /**
     *
     * @param  ProxyQueryInterface $queryBuilder
     * @param  type                $alias
     * @param  type                $field
     * @param  type                $data
     * @return type
     */
    protected function handleScalar(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (empty($data['value'])) {
            return;
        }

        $id = self::fixIdentifier($data['value']->getId());

        if (isset($data['type']) && $data['type'] == EqualType::TYPE_IS_NOT_EQUAL) {
            $queryBuilder->field($field)->notEqual($id);
        } else {
            $queryBuilder->field($field)->equals($id);
        }

        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function association(ProxyQueryInterface $queryBuilder, $data)
    {
        $associationMappings = $this->getParentAssociationMappings();
        $associationMappings[] = $this->getAssociationMapping();
        $alias = $queryBuilder->entityJoin($associationMappings);

        return array($alias, false);
    }

    /**
     * Return \MongoId if $id is MongoId in string representation, otherwise custom string
     *
     * @param  mixed           $id
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
     * Identifier field name is 'field' if mapping type is simple; otherwise, it's 'field.$id'
     *
     * @param  string $field
     * @return string
     */
    protected function getIdentifierField($field)
    {
        $field_mapping = $this->getFieldMapping();

        return (true === $field_mapping['simple']) ? $field : $field . '.$id';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'field_name'   => false,
            'field_type'  => 'sonata_type_model_autocomplete',
            'field_options' => array(),
            'operator_type' => 'sonata_type_equal',
            'operator_options' => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('sonata_type_filter_default', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label'         => $this->getLabel()
        ));
    }
}

