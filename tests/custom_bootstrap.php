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

use Sonata\DoctrineMongoDBAdminBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

$application = new Application(new AppKernel());
$application->setAutoExit(false);

// Load fixtures of the AppTestBundle
$input = new ArrayInput([
    'command' => 'doctrine:mongodb:fixtures:load',
    '--no-interaction' => false,
]);
$application->run($input, new NullOutput());

// Install Assets
$input = new ArrayInput([
    'command' => 'assets:install',
    'target' => __DIR__.'/App/public',
    '--symlink' => true,
]);
$application->run($input, new NullOutput());
