<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Monolog\Level;
use Psr\Log\LogLevel;

final class LogEventListener extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(LogLevel::DEBUG, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::INFO, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::WARNING, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::ERROR, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::CRITICAL, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::ALERT, [$this, 'onLog']);
        $this->listeners[] = $events->attach(LogLevel::EMERGENCY, [$this, 'onLog']);
    }

    public function onLog($event): void
    {
        $message = $event->getParam('message');
        $level = $event->getParam('level');
        $context = $event->getParam('context');
        echo sprintf('[%s] %s: %s', $level, $message, json_encode($context)) . PHP_EOL;
    }
}
