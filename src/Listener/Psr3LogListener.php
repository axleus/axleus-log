<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\Mvc\Controller\AbstractController;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class Psr3LogListener extends AbstractListenerAggregate
{
    private array $identifiers = [
        AbstractController::class,
        MiddlewareInterface::class,
        RequestHandlerInterface::class,
    ];

    public function __construct(
        private LoggerInterface $logger
    ) {
    }
}
