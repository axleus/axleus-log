<?php

declare(strict_types=1);

/**
 * This file is part of the Webware Log package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webware\Log\Listener;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Webware\Log\Event\LogEvent;
use Webware\Log\LogChannel;

final class Psr3LogPsr14Listener
{
    public function __construct(
        private LoggerInterface&Logger $logger,
    ) {}

    public function __invoke(LogEvent $event): void
    {
        $channel = $event->getChannel();

        if (LogChannel::App !== $channel) {
            $this->logger = $this->logger->withName($channel->value);
        }

        $this->logger->log(
            $event->getLevel()->toPsrLogLevel(),
            $event->getMessage(),
            $event->getContext(),
        );
    }
}
