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

use Axleus\Log\ConfigProvider;
use Axleus\Log\LogChannel;
use Laminas\EventManager\Event;
use Monolog\Level;
use Psr\Log\LogLevel;

class LogEvent extends Event
{
    final public const EVENT_LOG = 'log';

    final public const EVENT_LOG_DEBUG = LogLevel::DEBUG;

    final public const EVENT_LOG_INFO = LogLevel::INFO;

    final public const EVENT_LOG_WARNING = LogLevel::WARNING;

    final public const EVENT_LOG_ERROR = LogLevel::ERROR;

    final public const EVENT_LOG_CRITICAL = LogLevel::CRITICAL;

    final public const EVENT_LOG_ALERT = LogLevel::ALERT;

    final public const EVENT_LOG_EMERGENCY = LogLevel::EMERGENCY;

    public function __construct(Level $name = Level::Debug, $target = null, array $params = [])
    {
        parent::__construct($name->toPsrLogLevel(), null, $params);
    }

    // todo improve this method
    public function setLevel(Level $level): self
    {
        $this->setParam('level', $level->toPsrLogLevel());

        return $this;
    }

    public function getLevel(): Level
    {
        return $this->getParam('level', Level::Debug);
    }

    public function setMessage(string $message): self
    {
        $this->setParam('message', $message);

        return $this;
    }

    public function getMessage(): string
    {
        return $this->getParam('message', '');
    }

    public function setExtra(array $extra): self
    {
        $this->setParam('extra', $extra);

        return $this;
    }

    public function getExtra(): array
    {
        return $this->getParam('extra', []);
    }

    public function setChannel(LogChannel $channel): self
    {
        $this->setParam('channel', $channel);

        return $this;
    }

    public function getChannel(): LogChannel
    {
        $fromConfig = (new ConfigProvider())->getAxleusConfig()['channel'];

        return $this->getParam(
            'channel',
            LogChannel::tryFrom($fromConfig)
        );
    }

    public function setUuid(string $uuid): self
    {
        $this->setParam('uuid', $uuid);

        return $this;
    }

    public function getUuid(): string
    {
        return $this->getParam('uuid', '');
    }

    public function setContext(array $context): self
    {
        $this->setParam('context', $context);

        return $this;
    }

    public function getContext(): array
    {
        return $this->getParam('context', []);
    }
}
