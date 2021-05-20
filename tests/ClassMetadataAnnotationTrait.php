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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

trait ClassMetadataAnnotationTrait
{
    /**
     * @phpstan-param class-string $class
     */
    protected function getMetadataForDocumentWithAnnotations(string $class): ClassMetadata
    {
        $classMetadata = new ClassMetadata($class);
        $reader = new AnnotationReader();

        $annotationDriver = new AnnotationDriver($reader);
        $annotationDriver->loadMetadataForClass($class, $classMetadata);

        return $classMetadata;
    }
}
