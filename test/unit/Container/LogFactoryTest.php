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

namespace AxleusTest\Log\Container;

use Axleus\Log\Container\LogFactory;
use Axleus\Log\Handler\LaminasDbHandler;
use Axleus\Log\Processor\LaminasI18nProcessor;
use Laminas\Translator\TranslatorInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(LogFactory::class)]
#[CoversMethod(LogFactory::class, '__invoke')]
final class LogFactoryTest extends TestCase
{
    #[Test]
    public function invokeReturnsLoggerInterface(): void
    {
        $handler   = $this->createStub(HandlerInterface::class);
        $container = $this->makeContainer(
            [],
            [LaminasDbHandler::class => $handler],
        );

        $factory = new LogFactory();
        $logger  = $factory($container);

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    #[Test]
    public function invokeUsesConfiguredChannel(): void
    {
        $handler   = $this->createStub(HandlerInterface::class);
        $container = $this->makeContainer(
            [LoggerInterface::class => ['channel' => 'security', 'log_errors' => false, 'process_uuid' => false, 'process_translation' => false, 'table' => 'log', 'auth_attribute' => 'attr']],
            [LaminasDbHandler::class => $handler],
        );

        $factory = new LogFactory();

        /** @var Logger $logger */
        $logger = $factory($container);

        $this->assertSame('security', $logger->getName());
    }

    #[Test]
    public function invokeFallsBackToDefaultsWhenNoConfig(): void
    {
        $handler   = $this->createStub(HandlerInterface::class);
        $container = $this->makeContainer(
            [],
            [LaminasDbHandler::class => $handler],
        );

        $factory = new LogFactory();

        /** @var Logger $logger */
        $logger = $factory($container);

        $this->assertSame('app', $logger->getName());
    }

    #[Test]
    public function invokePushesTranslationProcessorWhenConfigured(): void
    {
        $handler   = $this->createStub(HandlerInterface::class);
        $processor = $this->createStub(ProcessorInterface::class);

        $container = $this->makeContainer(
            [LoggerInterface::class => ['channel' => 'app', 'log_errors' => false, 'process_uuid' => false, 'process_translation' => true, 'table' => 'log', 'auth_attribute' => 'attr']],
            [
                LaminasDbHandler::class     => $handler,
                TranslatorInterface::class  => $this->createStub(TranslatorInterface::class),
                LaminasI18nProcessor::class => $processor,
            ],
        );

        $factory = new LogFactory();

        /** @var Logger $logger */
        $logger = $factory($container);

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $services
     */
    private function makeContainer(array $config, array $services = []): ContainerInterface
    {
        $container = $this->createStub(ContainerInterface::class);

        $container->method('get')->willReturnCallback(
            static function (string $id) use ($config, $services): mixed {
                if ($id === 'config') {
                    return $config;
                }

                return $services[$id] ?? null;
            }
        );

        $container->method('has')->willReturnCallback(
            static fn (string $id): bool => isset($services[$id])
        );

        return $container;
    }
}
