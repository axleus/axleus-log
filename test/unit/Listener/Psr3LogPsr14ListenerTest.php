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

namespace AxleusTest\Log\Listener;

use Axleus\Log\Event\LogEvent;
use Axleus\Log\Listener\Psr3LogPsr14Listener;
use Axleus\Log\LogChannel;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Psr3LogPsr14Listener::class)]
final class Psr3LogPsr14ListenerTest extends TestCase
{
    private Logger&MockObject $logger;
    private Psr3LogPsr14Listener $listener;

    protected function setUp(): void
    {
        $this->logger   = $this->createMock(Logger::class);
        $this->listener = new Psr3LogPsr14Listener($this->logger);
    }

    #[Test]
    public function invokeCallsLogWithCorrectLevelMessageAndContext(): void
    {
        $event = new LogEvent(LogChannel::App, Level::Info);
        $event->setMessage('Hello world');
        $event->setContext(['user' => 'alice']);

        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                Level::Info->toPsrLogLevel(),
                'Hello world',
                ['user' => 'alice'],
            );

        ($this->listener)($event);
    }

    #[Test]
    public function invokeDoesNotCallWithNameForAppChannel(): void
    {
        $event = new LogEvent(LogChannel::App, Level::Debug);
        $event->setMessage('msg');

        $this->logger
            ->expects($this->never())
            ->method('withName');

        $this->logger
            ->expects($this->once())
            ->method('log');

        ($this->listener)($event);
    }

    #[Test]
    public function invokeCallsWithNameForNonAppChannel(): void
    {
        $event = new LogEvent(LogChannel::Error, Level::Error);
        $event->setMessage('something failed');

        $renamedLogger = $this->createMock(Logger::class);
        $renamedLogger->expects($this->once())->method('log');

        $this->logger
            ->expects($this->once())
            ->method('withName')
            ->with(LogChannel::Error->value)
            ->willReturn($renamedLogger);

        ($this->listener)($event);
    }

    #[Test]
    public function invokePassesEmptyContextWhenNotSet(): void
    {
        $event = new LogEvent(LogChannel::App, Level::Warning);
        $event->setMessage('warn msg');

        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                Level::Warning->toPsrLogLevel(),
                'warn msg',
                [],
            );

        ($this->listener)($event);
    }

    #[Test]
    public function invokeUsesRenamedLoggerForLogging(): void
    {
        $event = new LogEvent(LogChannel::Security, Level::Critical);
        $event->setMessage('breach detected');

        $renamedLogger = $this->createMock(Logger::class);

        $this->logger
            ->method('withName')
            ->with(LogChannel::Security->value)
            ->willReturn($renamedLogger);

        // The renamed logger must be the one that calls log(), not the original
        $renamedLogger
            ->expects($this->once())
            ->method('log')
            ->with(
                Level::Critical->toPsrLogLevel(),
                'breach detected',
                [],
            );

        $this->logger->expects($this->never())->method('log');

        ($this->listener)($event);
    }
}
