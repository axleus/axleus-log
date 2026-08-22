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
use Monolog\Level;
use Monolog\LogRecord;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Log\Processor\RamseyUuidProcessor;

use function is_string;
use function strlen;

#[CoversClass(RamseyUuidProcessor::class)]
#[CoversMethod(RamseyUuidProcessor::class, '__invoke')]
final class RamseyUuidProcessorTest extends TestCase
{
    private RamseyUuidProcessor $processor;

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeAddsUuidToExtra(): void
    {
        $record = $this->makeRecord();
        $result = ($this->processor)($record);

        $this->assertArrayHasKey('uuid', $result->extra);
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
    public function invokeUuidIsNonEmptyString(): void
    {
        $record = $this->makeRecord();
        $result = ($this->processor)($record);

        $this->assertTrue(is_string($result->extra['uuid']));
        $this->assertGreaterThan(0, strlen($result->extra['uuid']));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeUuidIsUniqueForDifferentDatetimes(): void
    {
        $record1 = $this->makeRecord(new DateTimeImmutable('2025-01-01 00:00:00.000000'));
        $record2 = $this->makeRecord(new DateTimeImmutable('2025-06-01 12:00:00.000000'));

        $result1 = ($this->processor)($record1);
        $result2 = ($this->processor)($record2);

        $this->assertNotSame($result1->extra['uuid'], $result2->extra['uuid']);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->processor = new RamseyUuidProcessor();
    }

    private function makeRecord(
        DateTimeImmutable $datetime = new DateTimeImmutable(),
        string $message = 'test',
    ): LogRecord {
        return new LogRecord(
            datetime: $datetime,
            channel : 'test',
            level   : Level::Debug,
            message : $message,
        );
    }
}
