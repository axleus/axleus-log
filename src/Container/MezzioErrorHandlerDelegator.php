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

namespace Axleus\Log\Container;

use Axleus\Log\ConfigProvider;
use Axleus\Log\Listener\MezzioErrorListener;
use Axleus\Log\LogChannel;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type LogDefaults from ConfigProvider
 */
final class MezzioErrorHandlerDelegator
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): ErrorHandler
    {
        /** @var array{LoggerInterface::class?: LogDefaults}&array<string, mixed> */
        $rawConfig = $container->get('config');

        /** @var LogDefaults $config */
        $config = $rawConfig[LoggerInterface::class] ?? (new ConfigProvider())->getConfigDefaults();

        /** @var ErrorHandler $handler */
        $handler = $callback();
        if (! $config['log_errors']) {
            return $handler;
        }

        /** @var Logger $logger */
        $logger   = $container->get(LoggerInterface::class);
        $listener = new MezzioErrorListener(
            $logger->withName(LogChannel::Error->value)
        );
        $handler->attachListener($listener);

        return $handler;
    }
}
