<?php

declare(strict_types=1);

/**
 * This file is part of the Webware Log package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WebwareTestIntegration\Log\Extension;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

use function extension_loaded;
use Override;

final class ListenerExtension implements Extension
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Override]
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters,
    ): void {
        if (extension_loaded('pdo_mysql')) {
            $facade->registerSubscribers(
                new IntegrationTestStartedListener(),
                new IntegrationTestStoppedListener(),
            );
        }
    }
}
