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

use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and will be removed in version 4.0
 */
abstract class AbstractTypeGuesser implements TypeGuesserInterface
{
    /**
     * @deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and will be removed in version 4.0.
     *
     * @param string $baseClass
     * @param string $propertyFullName
     *
     * @return array|null
     */
    protected function getParentMetadataForProperty($baseClass, $propertyFullName, ModelManager $modelManager)
    {
        if ('sonata_deprecation_mute' !== (\func_get_args()[3] ?? null)) {
            @trigger_error(sprintf(
                'The "%s()" method is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.x and'
                .' will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        try {
            return $modelManager->getParentMetadataForProperty($baseClass, $propertyFullName, 'sonata_deprecation_mute');
        } catch (MappingException $e) {
            // no metadata found.
            return null;
        }
    }
}
