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

namespace WebwareTest\Log\Processor;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Translator\TranslatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Webware\Log\Processor\LaminasI18nProcessor;
use Webware\Log\Processor\LaminasI18nProcessorFactory;

#[CoversClass(LaminasI18nProcessorFactory::class)]
#[CoversMethod(LaminasI18nProcessorFactory::class, '__invoke')]
final class LaminasI18nProcessorFactoryTest extends TestCase
{
    #[Test]
    public function invokeReturnsLaminasI18nProcessorWhenTranslatorPresent(): void
    {
        $translator = $this->createStub(\Laminas\I18n\Translator\TranslatorInterface::class);
        $container  = $this->createStub(ContainerInterface::class);

        $container->method('has')
            ->willReturnCallback(
                static fn(string $id): bool => $id === TranslatorInterface::class,
            );

        $container->method('get')
            ->willReturnCallback(
                static fn(string $id): mixed => $id === TranslatorInterface::class ? $translator : null,
            );

        $factory = new LaminasI18nProcessorFactory();
        $result  = $factory($container);

        $this->assertInstanceOf(LaminasI18nProcessor::class, $result);
    }

    #[Test]
    public function invokeThrowsWhenTranslatorNotInContainer(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(
                static fn(string $id): bool => false,
            );

        $factory = new LaminasI18nProcessorFactory();

        $this->expectException(ServiceNotFoundException::class);

        $factory($container);
    }
}
