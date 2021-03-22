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

namespace Sonata\DoctrineMongoDBAdminBundle\Guesser;

// NEXT_MAJOR: Remove this file.
if (!class_exists(\Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser::class, false)) {
    @trigger_error(sprintf(
        'The %s\FilterTypeGuesser class is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and will be removed in 4.0.'
        .' Use \Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser instead.',
        __NAMESPACE__
    ), \E_USER_DEPRECATED);
}

class_alias(
    \Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser::class,
    __NAMESPACE__.'\FilterTypeGuesser'
);

if (false) {
    /**
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x, to be removed in 4.0.
     * Use Sonata\DoctrineMongoDBAdminBundle\Guesser\FilterTypeGuesser instead.
     */
    class FilterTypeGuesser extends \Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser
    {
    }
}
