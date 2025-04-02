<?php

declare(strict_types=1);

namespace Axleus\Log\Middleware;

use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

final class MonologMiddlewareFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): MonologMiddleware
    {
        /** @var Logger $logger */
        $logger = $container->get(LoggerInterface::class);
        return new MonologMiddleware($logger);
    }
}
