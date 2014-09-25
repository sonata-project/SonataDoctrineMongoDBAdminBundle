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

class CallbackFilter extends Filter
{
    /**
     * @throws \RuntimeException
     * @param  ProxyQueryInterface $queryBuilder
     * @param  string              $alias
     * @param  string              $field
     * @param  string              $data
     * @return void
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!is_callable($this->getOption('callback'))) {
            throw new \RuntimeException(sprintf('Please provide a valid callback option "filter" for field "%s"', $this->getName()));
        }

        call_user_func($this->getOption('callback'), $queryBuilder, $alias, $field, $data);

        if (is_callable($this->getOption('active_callback'))) {
            $this->active = call_user_func($this->getOption('active_callback'), $data);
            return;
        }

        $this->active = true;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return array(
            'callback' => null,
            'active_callback' => function($data) {
                return isset($data['value']) && $data['value'];
            },
            'field_type' => 'text',
            'operator_type' => 'hidden',
            'operator_options' => array()
        );
    }

    public function getRenderSettings()
    {
        return array('sonata_type_filter_default', array(
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'operator_type' => $this->getOption('operator_type'),
                'operator_options' => $this->getOption('operator_options'),
                'label' => $this->getLabel()
        ));
    }
}
