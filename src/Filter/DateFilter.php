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

use Symfony\Component\Form\Extension\Core\Type\DateType;

final class DateFilter extends AbstractDateFilter
{
    /**
     * This filter has no range.
     *
     * @var bool
     */
    protected $range = false;

    /**
     * This filter does not allow filtering by time.
     *
     * @var bool
     */
    protected $time = false;

    protected function getDateFieldType(): string
    {
        return DateType::class;
    }
}
