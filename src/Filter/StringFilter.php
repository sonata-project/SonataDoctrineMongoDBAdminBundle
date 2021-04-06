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

use MongoDB\BSON\Regex;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class StringFilter extends Filter
{
    /**
     * NEXT_MAJOR: Remove $alias parameter.
     *
     * @return void
     */
    public function filter(BaseProxyQueryInterface $query, $alias, $field, $data)
    {
        /* NEXT_MAJOR: Remove this deprecation and update the typehint */
        if (!$query instanceof ProxyQueryInterface) {
            @trigger_error(sprintf(
                'Passing %s as argument 1 to %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8'
                .' and will throw a \TypeError error in version 4.0. You MUST pass an instance of %s instead.',
                \get_class($query),
                __METHOD__,
                ProxyQueryInterface::class
            ), \E_USER_DEPRECATED);
        }

        if (!$data || !\is_array($data) || !\array_key_exists('value', $data) || null === $data['value']) {
            return;
        }

        $data['value'] = trim($data['value']);

        if ('' === $data['value']) {
            return;
        }

        $data['type'] = isset($data['type']) && !empty($data['type']) ? $data['type'] : ContainsOperatorType::TYPE_CONTAINS;

        $obj = $query->getQueryBuilder();
        if (self::CONDITION_OR === $this->condition) {
            $obj = $query->getQueryBuilder()->expr();
        }

        if (ContainsOperatorType::TYPE_EQUAL === $data['type']) {
            $obj->field($field)->equals($data['value']);
        } elseif (ContainsOperatorType::TYPE_CONTAINS === $data['type']) {
            $obj->field($field)->equals(new Regex($data['value'], 'i'));
        } elseif (ContainsOperatorType::TYPE_NOT_CONTAINS === $data['type']) {
            $obj->field($field)->not(new Regex($data['value'], 'i'));
        }

        if (self::CONDITION_OR === $this->condition) {
            $query->getQueryBuilder()->addOr($obj);
        }

        $this->active = true;
    }

    public function getDefaultOptions()
    {
        return [];
    }

    public function getRenderSettings()
    {
        return [ChoiceType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }
}
