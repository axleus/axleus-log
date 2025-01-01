<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Monolog\Level;
use Psr\Log\LogLevel;

final class Psr3LogListener extends AbstractListenerAggregate
{
    public function __construct(
        private LoggerInterface $logger,
        private ?AuthenticationService $auth = null
    ) {}

    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(LogLevel::ALERT, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::CRITICAL, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::DEBUG, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::EMERGENCY, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::ERROR, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::INFO, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::WARNING, [$this, 'onLog']);
    }

    public function onLog($event): void
    {
        $message = $event->getParam('message');
        $level   = $event->getParam('level');
        $context = $event->getParam('context');
        $this->logger->log($level, $message, $context);
    }
}
