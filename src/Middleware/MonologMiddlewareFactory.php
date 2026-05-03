<?php

declare(strict_types=1);

/**
 * This file is part of the Axleus Log package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axleus\Log\Middleware;

use Axleus\Log\ConfigProvider;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type LogDefaults from ConfigProvider
 */
class MonologMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): MonologMiddleware
    {
        /** @var Logger */
        $logger = $container->get(LoggerInterface::class);

        /** @var array{LoggerInterface::class?: LogDefaults}&array<string, mixed> */
        $rawConfig = $container->get('config');

        /** @var LogDefaults $logConfig */
        $logConfig     = $rawConfig[LoggerInterface::class] ?? (new ConfigProvider())->getConfigDefaults();
        $authAttribute = $logConfig['auth_attribute'];

        return new MonologMiddleware($logger, $authAttribute);
    }
}
