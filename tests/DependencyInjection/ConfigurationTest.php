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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Sonata\DoctrineMongoDBAdminBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testDefaultOptions(): void
    {
        $this->assertProcessedConfigurationEquals([], [
            'templates' => [],
        ]);
    }

    public function testCustomTemplates(): void
    {
        $this->assertProcessedConfigurationEquals([[
            'templates' => [
                'types' => [
                    'list' => [
                        'array' => 'list_array.twig.html',
                    ],
                    'show' => [
                        'array' => 'show_array.twig.html',
                    ],
                ],
            ],
        ]], [
            'templates' => [
                'types' => [
                    'list' => [
                        'array' => 'list_array.twig.html',
                    ],
                    'show' => [
                        'array' => 'show_array.twig.html',
                    ],
                ],
            ],
        ]);
    }

    public function testTemplateTypesWithInvalidValues(): void
    {
        $this->assertConfigurationIsInvalid(
            [[
                'templates' => [
                    'types' => [
                        'edit' => [],
                    ],
                ],
            ]],
            'edit'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
