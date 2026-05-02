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

namespace Axleus\Log\Event;

use Axleus\Log\LogChannel;
use Monolog\Level;
use Override;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LogLevel;

class LogEvent implements StoppableEventInterface
{
    final public const EVENT_LOG = 'log';

    final public const EVENT_LOG_DEBUG = LogLevel::DEBUG;

    final public const EVENT_LOG_INFO = LogLevel::INFO;

    final public const EVENT_LOG_WARNING = LogLevel::WARNING;

    final public const EVENT_LOG_ERROR = LogLevel::ERROR;

    final public const EVENT_LOG_CRITICAL = LogLevel::CRITICAL;

    final public const EVENT_LOG_ALERT = LogLevel::ALERT;

    final public const EVENT_LOG_EMERGENCY = LogLevel::EMERGENCY;

    private bool $propagationStopped = false;

    private Level $level;

    private string $message = '';

    private array $extra = [];

    private string $uuid = '';

    private array $context = [];

    public function __construct(
        private LogChannel $channel = LogChannel::App,
        Level $level = Level::Debug,
    ) {
        $this->level = $level;
    }

    #[Override]
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    public function setLevel(Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): Level
    {
        return $this->level;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setExtra(array $extra): self
    {
        $this->extra = $extra;

        return $this;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }

    public function setChannel(LogChannel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function getChannel(): LogChannel
    {
        return $this->channel;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
