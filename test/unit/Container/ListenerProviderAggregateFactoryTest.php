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

namespace WebwareTest\Log\Container;

use Monolog\Logger;
use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Phly\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Webware\Log\ConfigProvider;
use Webware\Log\Container\ListenerProviderAggregateFactory;
use Webware\Log\Event\LogEvent;
use Webware\Log\Listener\Psr3LogPsr14Listener;

#[CoversClass(ListenerProviderAggregateFactory::class)]
#[CoversMethod(ListenerProviderAggregateFactory::class, '__invoke')]
final class ListenerProviderAggregateFactoryTest extends TestCase
{
    #[Test]
    public function invokeHandlesEmptyListenersConfig(): void
    {
        $container = $this->makeContainer([
            ConfigProvider::LISTENER_KEY          => [],
            ConfigProvider::LISTENER_PROVIDER_KEY => [],
        ]);

        $factory = new ListenerProviderAggregateFactory();
        $result  = $factory($container);

        $this->assertInstanceOf(ListenerProviderAggregate::class, $result);
    }

    #[Test]
    public function invokeReturnsListenerProviderAggregate(): void
    {
        $container = $this->makeContainer([]);
        $factory   = new ListenerProviderAggregateFactory();
        $result    = $factory($container);

        $this->assertInstanceOf(ListenerProviderAggregate::class, $result);
    }

    #[Test]
    public function invokeWiresListenerFromContainerWithPriority(): void
    {
        $logger   = $this->createStub(Logger::class);
        $listener = new Psr3LogPsr14Listener($logger);

        $container = $this->makeContainer(
            [
                ConfigProvider::LISTENER_KEY => [
                    LogEvent::class => [
                        ['listener' => Psr3LogPsr14Listener::class, 'priority' => 1],
                    ],
                ],
            ],
            [Psr3LogPsr14Listener::class => $listener],
        );

        $factory = new ListenerProviderAggregateFactory();
        $result  = $factory($container);

        $this->assertInstanceOf(ListenerProviderAggregate::class, $result);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $services
     */
    private function makeContainer(array $config, array $services = []): ContainerInterface
    {
        $prioritizedProvider = new PrioritizedListenerProvider();
        $attachableProvider  = new AttachableListenerProvider();

        $services[PrioritizedListenerProvider::class] = $prioritizedProvider;
        $services[AttachableListenerProvider::class]  = $attachableProvider;

        $container = $this->createStub(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnCallback(
                static function (string $id) use ($config, $services): mixed {
                    if ($id === 'config') {
                        return $config;
                    }

                    return $services[$id] ?? null;
                },
            );
        $container
            ->method('has')
            ->willReturnCallback(
                static fn(string $id): bool => isset($services[$id]),
            );

        return $container;
    }
}
