<?php

declare(strict_types=1);

namespace Axleus\Log\Container;

use Axleus\Log\ConfigProvider;
use Axleus\Log\Listener\MezzioErrorListener;
use Axleus\Log\LogChannel;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class MezzioErrorHandlerDelegator
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): ErrorHandler
    {
        $config  = $container->get('config')[ConfigProvider::class];
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
