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

/*
 * DO NOT EDIT THIS FILE!
 *
 * It's auto-generated by sonata-project/dev-kit package.
 */
/*
 * fix encoding issue while running text on different host with different locale configuration
 */
setlocale(LC_ALL, 'en_US.UTF-8');

require_once __DIR__.'/../vendor/autoload.php';

if (file_exists($file = __DIR__.'/custom_bootstrap.php')) {
    require_once $file;
}
