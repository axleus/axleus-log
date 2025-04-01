<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

final class Psr3LogListenerFactory
{
    public function __invoke(ContainerInterface $container): Psr3LogListener
    {
        return new Psr3LogListener(
            $container->get(LoggerInterface::class),
        );
    }
}
