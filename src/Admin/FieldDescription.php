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

namespace Sonata\DoctrineMongoDBAdminBundle\Admin;

// NEXT_MAJOR: Remove this file.
if (!class_exists(\Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription::class, false)) {
    @trigger_error(sprintf(
        'The %s\FieldDescription class is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8 and will be removed in 4.0.'
        .' Use \Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription instead.',
        __NAMESPACE__
    ), \E_USER_DEPRECATED);
}

class_alias(
    \Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription::class,
    __NAMESPACE__.'\FieldDescription'
);

if (false) {
    /**
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8, to be removed in 4.0.
     * Use Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription instead.
     */
    class FieldDescription extends \Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription
    {
    }
}
