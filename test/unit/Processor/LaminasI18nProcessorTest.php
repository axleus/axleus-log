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

use DateTimeImmutable;
use Laminas\I18n\Translator\TranslatorInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Webware\Log\Processor\LaminasI18nProcessor;

#[CoversClass(LaminasI18nProcessor::class)]
#[CoversMethod(LaminasI18nProcessor::class, '__invoke')]
final class LaminasI18nProcessorTest extends TestCase
{
    private LaminasI18nProcessor $processor;

    /**
     * @throws \PHPUnit\Exception
     */
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

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeReturnsLogRecord(): void
    {
        $record = $this->makeRecord();
        $result = ($this->processor)($record);

        $this->assertInstanceOf(LogRecord::class, $result);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeReturnsRecordUnchangedWhenNoTranslatorSet(): void
    {
        $record = $this->makeRecord('original message');
        $result = ($this->processor)($record);

        $this->assertSame('original message', $result->message);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeTranslatesMessageWhenTranslatorIsSet(): void
    {
        /** @var MockObject&TranslatorInterface $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('translate')
            ->with('Hello world')
            ->willReturn('Hola mundo');

        $this->processor->setTranslator($translator);

        $record = $this->makeRecord('Hello world');
        $result = ($this->processor)($record);

        $this->assertSame('Hola mundo', $result->message);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->processor = new LaminasI18nProcessor();
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
