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

use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Compiler\AddGuesserCompilerPass;
use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Compiler\AddTemplatesCompilerPass;
use Sonata\DoctrineMongoDBAdminBundle\SonataDoctrineMongoDBAdminBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
class SonataDoctrineMongoDBAdminBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(AddGuesserCompilerPass::class));

        $containerBuilder
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(AddTemplatesCompilerPass::class));

        $bundle = new SonataDoctrineMongoDBAdminBundle();
        $bundle->build($containerBuilder);
    }
}
