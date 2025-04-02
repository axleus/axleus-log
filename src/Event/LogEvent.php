<?php

declare(strict_types=1);

namespace Axleus\Log\Event;

use Axleus\Log\ConfigProvider;
use Axleus\Log\LogChannel;
use Laminas\EventManager\Event;
use Monolog\Level;
use Psr\Log\LogLevel;

class LogEvent extends Event
{
    public final const EVENT_LOG           = 'log';
    public final const EVENT_LOG_DEBUG     = LogLevel::DEBUG;
    public final const EVENT_LOG_INFO      = LogLevel::INFO;
    public final const EVENT_LOG_WARNING   = LogLevel::WARNING;
    public final const EVENT_LOG_ERROR     = LogLevel::ERROR;
    public final const EVENT_LOG_CRITICAL  = LogLevel::CRITICAL;
    public final const EVENT_LOG_ALERT     = LogLevel::ALERT;
    public final const EVENT_LOG_EMERGENCY = LogLevel::EMERGENCY;

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
