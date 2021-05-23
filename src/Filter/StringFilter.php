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

use Doctrine\ODM\MongoDB\Query\Expr;
use MongoDB\BSON\Regex;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class StringFilter extends Filter
{
    public function getDefaultOptions(): array
    {
        return ['field_type' => TextType::class];
    }

    public function getRenderSettings(): array
    {
        return [ChoiceType::class, [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ]];
    }

    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || null === $data->getValue()) {
            return;
        }

        $value = trim($data->getValue());

        if ('' === $value) {
            return;
        }

        $type = $data->getType() ?? ContainsOperatorType::TYPE_CONTAINS;

        $obj = $query->getQueryBuilder();
        if (self::CONDITION_OR === $this->condition) {
            $obj = $query->getQueryBuilder()->expr();
        }

        if (ContainsOperatorType::TYPE_EQUAL === $type) {
            $obj->field($field)->equals($value);
        } elseif (ContainsOperatorType::TYPE_CONTAINS === $type) {
            $obj->field($field)->equals(new Regex($value, 'i'));
        } elseif (ContainsOperatorType::TYPE_NOT_CONTAINS === $type) {
            $obj->field($field)->not(new Regex($value, 'i'));
        }

        if (self::CONDITION_OR === $this->condition) {
            \assert($obj instanceof Expr);
            $query->getQueryBuilder()->addOr($obj);
        }

        $this->setActive(true);
    }
}
