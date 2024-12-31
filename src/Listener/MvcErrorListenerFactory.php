<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Laminas\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class MvcErrorListenerFactory
{
    public function __invoke(ContainerInterface $container): MvcErrorListener
    {
        return new MvcErrorListener(
            $container->get(LoggerInterface::class),
            $container->has(AuthenticationService::class)
            ? $container->get(AuthenticationService::class)
            : null,
        );
    }
}
