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

abstract class AbstractTypeGuesser implements TypeGuesserInterface
{
    /**
     * @param string $baseClass
     * @param string $propertyFullName
     *
     * @return array|null
     */
    protected function getParentMetadataForProperty($baseClass, $propertyFullName, ModelManager $modelManager)
    {
        try {
            return $modelManager->getParentMetadataForProperty($baseClass, $propertyFullName);
        } catch (MappingException $e) {
            // no metadata found.
            return null;
        }
    }
}
