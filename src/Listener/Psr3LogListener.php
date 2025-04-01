<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Axleus\Log\Event\LogEvent;
use Axleus\Log\LogChannel;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Monolog\Level;
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
