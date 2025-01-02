<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Axleus\Log\Event\LogEvent;
use Axleus\Log\LogChannel;
use Laminas\Authentication\AuthenticationService;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Mezzio\Authentication\AuthenticationInterface;
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
        private LoggerInterface $logger,
        private AuthenticationInterface|AuthenticationService|null $auth = null
    ) {}

    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $events = $events->getSharedManager();
        foreach ($this->identifiers as $identifier) {
            $this->listeners[] = $events->attach($identifier, LogEvent::EVENT_LOG, [$this, 'onLog']);
        }
        foreach (Level::cases() as $level) {
            foreach ($this->identifiers as $identifier) {
                $this->listeners[] = $events->attach($identifier, $level->toPsrLogLevel(), [$this, 'onLog']);
            }
        }
    }

    public function onLog(EventInterface $event): void
    {
        $channel = $event->getParam('channel', LogChannel::App);
        if ($channel !== LogChannel::App) {
            $this->logger = $this->logger->withName($channel->value);
        }
        $this->logger->log(
            $event->getParam('level')->toPsrLogLevel(),
            $event->getParam('message'),
            $event->getParam('context', []),
        );
    }
}
