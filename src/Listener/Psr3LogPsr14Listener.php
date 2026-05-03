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
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class Psr3LogPsr14Listener
{
    public function __construct(
        private LoggerInterface&Logger $logger,
    ) {}

    public function __invoke(LogEvent $event): void
    {
        $channel = $event->getChannel();

        if ($channel !== LogChannel::App) {
            $this->logger = $this->logger->withName($channel->value);
        }

        $this->logger->log(
            $event->getLevel()->toPsrLogLevel(),
            $event->getMessage(),
            $event->getContext(),
        );
    }
}
