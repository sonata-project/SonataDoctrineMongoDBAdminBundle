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

class DateRangeFilter extends AbstractDateFilter
{
    /**
     * This is a range filter.
     *
     * @var bool
     */
    protected $range = true;

    /**
     * This filter has time.
     *
     * @var bool
     */
    protected $time = false;

    /**
     * {@inheritdoc}
     */
    public function getFieldType()
    {
        return $this->getOption('field_type', 'sonata_type_date_range');
    }
}
