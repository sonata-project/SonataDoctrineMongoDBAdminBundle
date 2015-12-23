<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;

class StringFilter extends Filter
{
    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string              $alias
     * @param string              $field
     * @param string              $data
     *
     * @return
     */
    public function filter(ProxyQueryInterface $queryBuilder, $name, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || strlen(trim($data['value'])) === 0) {
            return;
        }
        $data['value'] = trim($data['value']);

        $fieldMapping = $this->getFieldMapping();
        if ($fieldMapping['type'] === 'id' && \MongoId::isValid($data['value'])) {
            $queryBuilder->field($field)->equals(new \MongoId($data['value']));
        } else {
            $data['type'] = isset($data['type']) && !empty($data['type']) ? $data['type'] : ChoiceType::TYPE_CONTAINS;

            if ($data['type'] == ChoiceType::TYPE_EQUAL) {
                $queryBuilder->field($field)->equals($data['value']);
            } elseif ($data['type'] == ChoiceType::TYPE_CONTAINS) {
                $queryBuilder->field($field)->equals(new \MongoRegex(sprintf('/%s/i', $data['value'])));
            } elseif ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                $queryBuilder->field($field)->not(new \MongoRegex(sprintf('/%s/i', $data['value'])));
            }
        }

        $this->active = true;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array();
    }

    public function getRenderSettings()
    {
        return array('sonata_type_filter_choice', array(
                'field_type'    => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label'         => $this->getLabel(),
        ));
    }
}
