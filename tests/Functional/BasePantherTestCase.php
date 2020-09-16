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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\Functional;

use Sonata\DoctrineMongoDBAdminBundle\Tests\App\AppKernel;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class BasePantherTestCase extends PantherTestCase
{
    protected static function createFirefoxClient(): Client
    {
        return static::createPantherClient([
            'browser' => PantherTestCase::FIREFOX,
        ]);
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
