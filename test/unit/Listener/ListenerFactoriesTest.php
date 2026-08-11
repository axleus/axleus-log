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

namespace WebwareTest\Log\Listener;

use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Webware\Log\Listener\Psr3LogLaminasListener;
use Webware\Log\Listener\Psr3LogLaminasListenerFactory;
use Webware\Log\Listener\Psr3LogPsr14Listener;
use Webware\Log\Listener\Psr3LogPsr14ListenerFactory;

#[CoversClass(Psr3LogPsr14ListenerFactory::class)]
#[CoversClass(Psr3LogLaminasListenerFactory::class)]
#[CoversMethod(Psr3LogPsr14ListenerFactory::class, '__invoke')]
#[CoversMethod(Psr3LogLaminasListenerFactory::class, '__invoke')]
final class ListenerFactoriesTest extends TestCase
{
    #[Test]
    public function laminasFactoryReturnsLaminasListener(): void
    {
        $logger    = $this->createStub(Logger::class);
        $container = $this->makeContainer($logger);

        $factory = new Psr3LogLaminasListenerFactory();
        $result  = $factory($container);

        $this->assertInstanceOf(Psr3LogLaminasListener::class, $result);
    }

    #[Test]
    public function psr14FactoryReturnsPsr14Listener(): void
    {
        $logger    = $this->createStub(Logger::class);
        $container = $this->makeContainer($logger);

        $factory = new Psr3LogPsr14ListenerFactory();
        $result  = $factory($container);

        $this->assertInstanceOf(Psr3LogPsr14Listener::class, $result);
    }

    private function makeContainer(Logger $logger): ContainerInterface
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnCallback(
                static fn(string $id): mixed => $id === LoggerInterface::class ? $logger : null,
            );

        return $container;
    }
}
