<?php

declare(strict_types=1);

/**
 * This file is part of the Axleus Log package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axleus\Log\Listener;

use Axleus\Log\Event\LogEvent;
use Axleus\Log\LogChannel;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated since 0.1.0; will be removed in 0.2.0. Use Psr3LogPsr14Listener instead.
 */
final class Psr3LogLaminasListener extends AbstractListenerAggregate
{
    private array $identifiers = [
        MiddlewareInterface::class,
        RequestHandlerInterface::class,
    ];

    public function __construct(
        private LoggerInterface|Logger $logger,
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
            $event->getParam('extra', [])
        );
    }
}
