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

namespace WebwareTest\Log;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Webware\Log\ConfigProvider;
use Webware\Log\Event\LogEvent;
use Webware\Log\Listener\Psr3LogPsr14Listener;
use Webware\Log\LogChannel;

#[CoversClass(ConfigProvider::class)]
#[CoversMethod(ConfigProvider::class, '__invoke')]
#[CoversMethod(ConfigProvider::class, 'getConfigDefaults')]
#[CoversMethod(ConfigProvider::class, 'getListeners')]
#[CoversMethod(ConfigProvider::class, 'getDependencies')]
final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    #[Test]
    public function getConfigDefaultsChannelDefaultsToApp(): void
    {
        $defaults = $this->provider->getConfigDefaults();

        $this->assertSame(LogChannel::App->value, $defaults['channel']);
    }

    #[Test]
    public function getConfigDefaultsProcessFlagsDefaultToFalse(): void
    {
        $defaults = $this->provider->getConfigDefaults();

        $this->assertFalse($defaults['log_errors']);
        $this->assertFalse($defaults['process_uuid']);
        $this->assertFalse($defaults['process_translation']);
    }

    #[Test]
    public function getConfigDefaultsReturnsExpectedKeys(): void
    {
        $defaults = $this->provider->getConfigDefaults();

        $this->assertArrayHasKey('channel', $defaults);
        $this->assertArrayHasKey('log_errors', $defaults);
        $this->assertArrayHasKey('process_uuid', $defaults);
        $this->assertArrayHasKey('process_translation', $defaults);
        $this->assertArrayHasKey('table', $defaults);
    }

    #[Test]
    public function getConfigDefaultsTableDefaultsToLog(): void
    {
        $defaults = $this->provider->getConfigDefaults();

        $this->assertSame('log', $defaults['table']);
    }

    #[Test]
    public function getDependenciesAliasesEventDispatcherInterface(): void
    {
        $deps = $this->provider->getDependencies();

        $this->assertArrayHasKey(EventDispatcherInterface::class, $deps['aliases']);
    }

    #[Test]
    public function getDependenciesAliasesListenerProviderInterface(): void
    {
        $deps = $this->provider->getDependencies();

        $this->assertArrayHasKey(ListenerProviderInterface::class, $deps['aliases']);
    }

    #[Test]
    public function getListenersPsr14ListenerHasPriority(): void
    {
        $listeners = $this->provider->getListeners();

        $this->assertArrayHasKey('priority', $listeners[LogEvent::class][0]);
        $this->assertIsInt($listeners[LogEvent::class][0]['priority']);
    }

    #[Test]
    public function getListenersRegistersLogEventWithPsr14Listener(): void
    {
        $listeners = $this->provider->getListeners();

        $this->assertArrayHasKey(LogEvent::class, $listeners);
        $this->assertSame(Psr3LogPsr14Listener::class, $listeners[LogEvent::class][0]['listener']);
    }

    #[Test]
    public function invokeContainsListenerKey(): void
    {
        $config = ($this->provider)();

        $this->assertArrayHasKey(ConfigProvider::LISTENER_KEY, $config);
    }

    #[Test]
    public function invokeContainsListenerProviderKey(): void
    {
        $config = ($this->provider)();

        $this->assertArrayHasKey(ConfigProvider::LISTENER_PROVIDER_KEY, $config);
    }

    #[Test]
    public function invokeDoesNotContainLegacyConfigProviderKey(): void
    {
        $config = ($this->provider)();

        $this->assertArrayNotHasKey(ConfigProvider::class, $config);
    }

    #[Test]
    public function invokeDoesNotContainLogRuntime(): void
    {
        $config = ($this->provider)();

        $this->assertArrayNotHasKey('log_runtime', $config);
    }

    #[Test]
    public function invokeNestedLoggerConfigMatchesGetConfigDefaults(): void
    {
        $config = ($this->provider)();

        $this->assertSame($this->provider->getConfigDefaults(), $config[LoggerInterface::class]);
    }

    #[Test]
    public function invokeReturnsArrayKeyedOnLoggerInterface(): void
    {
        $config = ($this->provider)();

        $this->assertArrayHasKey(LoggerInterface::class, $config);
    }

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }
}
