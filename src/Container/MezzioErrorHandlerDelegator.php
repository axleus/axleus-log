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

use Axleus\Log\Listener\MezzioErrorListener;
use Axleus\Log\LogChannel;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class MezzioErrorHandlerDelegator
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): ErrorHandler
    {
        $config  = $container->get('config')[LoggerInterface::class];
        $handler = $callback();
        if (! $config['log_errors']) {
            return $handler;
        }
        $listener = new MezzioErrorListener(
            $container->get(LoggerInterface::class)->withName(LogChannel::Error->value)
        );
        $handler->attachListener($listener);

        return $handler;
    }
}
