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

namespace Sonata\DoctrineMongoDBAdminBundle;

use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Compiler\AddGuesserCompilerPass;
use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Compiler\AddTemplatesCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SonataDoctrineMongoDBAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddGuesserCompilerPass());
        $container->addCompilerPass(new AddTemplatesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);
    }
}
