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

namespace AxleusTest\Log;

use Axleus\Log\ConfigProvider;
use Axleus\Log\LogChannel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    #[Test]
    public function invokeReturnsArrayKeyedOnLoggerInterface(): void
    {
        $config = ($this->provider)();

        $this->assertArrayHasKey(LoggerInterface::class, $config);
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
    public function getConfigDefaultsTableDefaultsToLog(): void
    {
        $defaults = $this->provider->getConfigDefaults();

        $this->assertSame('log', $defaults['table']);
    }

    #[Test]
    public function invokeNestedLoggerConfigMatchesGetConfigDefaults(): void
    {
        $config = ($this->provider)();

        $this->assertSame($this->provider->getConfigDefaults(), $config[LoggerInterface::class]);
    }
}
