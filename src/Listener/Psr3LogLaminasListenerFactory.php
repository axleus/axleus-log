<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Laminas\Authentication\AuthenticationService;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

final class Psr3LogLaminasListenerFactory
{
    public function __invoke(ContainerInterface $container): Psr3LogLaminasListener
    {
        return new Psr3LogLaminasListener(
            $container->get(LoggerInterface::class)
        );
    }
}
