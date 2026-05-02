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

namespace AxleusTest\Log\Event;

use Axleus\Log\Event\LogEvent;
use Axleus\Log\LogChannel;
use Monolog\Level;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\StoppableEventInterface;

#[CoversClass(LogEvent::class)]
final class LogEventTest extends TestCase
{
    #[Test]
    public function implementsStoppableEventInterface(): void
    {
        $this->assertInstanceOf(StoppableEventInterface::class, new LogEvent());
    }

    #[Test]
    public function propagationIsNotStoppedByDefault(): void
    {
        $event = new LogEvent();

        $this->assertFalse($event->isPropagationStopped());
    }

    #[Test]
    public function stopPropagationSetsFlagToTrue(): void
    {
        $event = new LogEvent();
        $event->stopPropagation();

        $this->assertTrue($event->isPropagationStopped());
    }

    #[Test]
    public function defaultChannelIsApp(): void
    {
        $event = new LogEvent();

        $this->assertSame(LogChannel::App, $event->getChannel());
    }

    #[Test]
    public function defaultLevelIsDebug(): void
    {
        $event = new LogEvent();

        $this->assertSame(Level::Debug, $event->getLevel());
    }

    #[Test]
    public function constructorChannelIsRespected(): void
    {
        $event = new LogEvent(LogChannel::Error);

        $this->assertSame(LogChannel::Error, $event->getChannel());
    }

    #[Test]
    public function constructorLevelIsRespected(): void
    {
        $event = new LogEvent(LogChannel::App, Level::Critical);

        $this->assertSame(Level::Critical, $event->getLevel());
    }

    #[Test]
    public function setLevelRoundTrip(): void
    {
        $event = new LogEvent();
        $event->setLevel(Level::Warning);

        $this->assertSame(Level::Warning, $event->getLevel());
    }

    #[Test]
    public function setLevelReturnsSelf(): void
    {
        $event = new LogEvent();

        $this->assertSame($event, $event->setLevel(Level::Info));
    }

    #[Test]
    public function setMessageRoundTrip(): void
    {
        $event = new LogEvent();
        $event->setMessage('test message');

        $this->assertSame('test message', $event->getMessage());
    }

    #[Test]
    public function setMessageReturnsSelf(): void
    {
        $event = new LogEvent();

        $this->assertSame($event, $event->setMessage('x'));
    }

    #[Test]
    public function defaultMessageIsEmptyString(): void
    {
        $event = new LogEvent();

        $this->assertSame('', $event->getMessage());
    }

    #[Test]
    public function setContextRoundTrip(): void
    {
        $event = new LogEvent();
        $event->setContext(['key' => 'value']);

        $this->assertSame(['key' => 'value'], $event->getContext());
    }

    #[Test]
    public function setContextReturnsSelf(): void
    {
        $event = new LogEvent();

        $this->assertSame($event, $event->setContext([]));
    }

    #[Test]
    public function defaultContextIsEmptyArray(): void
    {
        $event = new LogEvent();

        $this->assertSame([], $event->getContext());
    }

    #[Test]
    public function setExtraRoundTrip(): void
    {
        $event = new LogEvent();
        $event->setExtra(['uuid' => 'abc-123']);

        $this->assertSame(['uuid' => 'abc-123'], $event->getExtra());
    }

    #[Test]
    public function setExtraReturnsSelf(): void
    {
        $event = new LogEvent();

        $this->assertSame($event, $event->setExtra([]));
    }

    #[Test]
    public function defaultExtraIsEmptyArray(): void
    {
        $event = new LogEvent();

        $this->assertSame([], $event->getExtra());
    }

    #[Test]
    public function setUuidRoundTrip(): void
    {
        $event = new LogEvent();
        $event->setUuid('550e8400-e29b-41d4-a716-446655440000');

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $event->getUuid());
    }

    #[Test]
    public function setUuidReturnsSelf(): void
    {
        $event = new LogEvent();

        $this->assertSame($event, $event->setUuid(''));
    }

    #[Test]
    public function defaultUuidIsEmptyString(): void
    {
        $event = new LogEvent();

        $this->assertSame('', $event->getUuid());
    }

    #[Test]
    public function setChannelRoundTrip(): void
    {
        $event = new LogEvent();
        $event->setChannel(LogChannel::Security);

        $this->assertSame(LogChannel::Security, $event->getChannel());
    }

    #[Test]
    public function setChannelReturnsSelf(): void
    {
        $event = new LogEvent();

        $this->assertSame($event, $event->setChannel(LogChannel::App));
    }

    /** @return array<string, array{LogChannel}> */
    public static function allChannelProvider(): array
    {
        return array_combine(
            array_map(static fn(LogChannel $c) => $c->name, LogChannel::cases()),
            array_map(static fn(LogChannel $c) => [$c], LogChannel::cases()),
        );
    }

    #[Test]
    #[DataProvider('allChannelProvider')]
    public function allLogChannelValuesCanBeSet(LogChannel $channel): void
    {
        $event = new LogEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
