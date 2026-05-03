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

namespace AxleusTest\Log\Processor;

use Axleus\Log\Processor\LaminasI18nProcessor;
use DateTimeImmutable;
use Laminas\Translator\TranslatorInterface;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(LaminasI18nProcessor::class)]
#[CoversMethod(LaminasI18nProcessor::class, '__invoke')]
#[CoversMethod(LaminasI18nProcessor::class, 'setTranslator')]
#[CoversMethod(LaminasI18nProcessor::class, 'getTranslator')]
#[CoversMethod(LaminasI18nProcessor::class, 'hasTranslator')]
#[CoversMethod(LaminasI18nProcessor::class, 'setTranslatorEnabled')]
#[CoversMethod(LaminasI18nProcessor::class, 'isTranslatorEnabled')]
#[CoversMethod(LaminasI18nProcessor::class, 'setTranslatorTextDomain')]
#[CoversMethod(LaminasI18nProcessor::class, 'getTranslatorTextDomain')]
final class LaminasI18nProcessorTest extends TestCase
{
    private LaminasI18nProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new LaminasI18nProcessor();
    }

    #[Test]
    public function invokeReturnsRecordUnchangedWhenNoTranslatorSet(): void
    {
        $record = $this->makeRecord('original message');
        $result = ($this->processor)($record);

        $this->assertSame('original message', $result->message);
    }

    #[Test]
    public function invokeTranslatesMessageWhenTranslatorIsSet(): void
    {
        /** @var MockObject&TranslatorInterface $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('translate')
            ->with('Hello world')
            ->willReturn('Hola mundo');

        $this->processor->setTranslator($translator);

        $record = $this->makeRecord('Hello world');
        $result = ($this->processor)($record);

        $this->assertSame('Hola mundo', $result->message);
    }

    #[Test]
    public function invokePreservesContextAndExtraWhenTranslating(): void
    {
        /** @var Stub&TranslatorInterface $translator */
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('translate')->willReturn('translated');
        $this->processor->setTranslator($translator);

        $record = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'original',
            context: ['key' => 'value'],
            extra: ['foo' => 'bar'],
        );

        $result = ($this->processor)($record);

        $this->assertSame(['key' => 'value'], $result->context);
        $this->assertSame(['foo' => 'bar'], $result->extra);
    }

    #[Test]
    public function invokeReturnsLogRecord(): void
    {
        $record = $this->makeRecord();
        $result = ($this->processor)($record);

        $this->assertInstanceOf(LogRecord::class, $result);
    }

    private function makeRecord(string $message = 'Hello world'): LogRecord
    {
        return new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: $message,
        );
    }
}
