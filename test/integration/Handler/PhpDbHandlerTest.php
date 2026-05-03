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

namespace AxleusTestIntegration\Log\Handler;

use Axleus\Log\Handler\PhpDbHandler;
use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use PDO;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Pdo\Connection;
use PhpDb\Mysql\Pdo\Driver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function getenv;
use function sprintf;

#[CoversClass(PhpDbHandler::class)]
#[CoversMethod(PhpDbHandler::class, 'handle')]
#[CoversMethod(PhpDbHandler::class, 'write')]
#[RequiresPhpExtension('pdo_mysql')]
final class PhpDbHandlerTest extends TestCase
{
    private AdapterInterface $adapter;

    private PDO $pdo;

    protected function setUp(): void
    {
        $hostname = (string) (getenv('TESTS_ADAPTER_MYSQL_HOSTNAME') ?: 'localhost');
        $username = (string) (getenv('TESTS_ADAPTER_MYSQL_USERNAME') ?: 'root');
        $password = (string) (getenv('TESTS_ADAPTER_MYSQL_PASSWORD') ?: '');
        $database = (string) (getenv('TESTS_ADAPTER_MYSQL_DATABASE') ?: 'axleus_log_test');
        $port     = (int) (getenv('TESTS_ADAPTER_MYSQL_PORT') ?: '3306');

        $connection = new Connection([
            'hostname' => $hostname,
            'port'     => $port,
            'username' => $username,
            'password' => $password,
            'database' => $database,
        ]);
        $driver        = new Driver($connection, new Statement(), new Result());
        $platform      = new AdapterPlatform($driver);
        $this->adapter = new Adapter($driver, $platform);

        $dsn       = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $hostname, $port, $database);
        $this->pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec('TRUNCATE TABLE `log`');
    }

    #[Test]
    public function writeInsertsLogRecord(): void
    {
        $handler = new PhpDbHandler($this->adapter, 'log');
        $record  = $this->makeRecord('integration test write');

        $handler->handle($record);

        $stmt = $this->pdo->prepare(
            'SELECT channel, level FROM log WHERE message = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['integration test write']);

        /** @var array{channel: string, level: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertSame('app', $row['channel']);
        $this->assertSame('INFO', $row['level']);
    }

    #[Test]
    public function writePopulatesUuidWhenPresent(): void
    {
        $handler = new PhpDbHandler($this->adapter, 'log');
        $record  = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'app',
            level: Level::Debug,
            message: 'uuid test',
            formatted: 'uuid test',
            extra: ['uuid' => 'test-uuid-value'],
        );

        $handler->handle($record);

        $stmt = $this->pdo->prepare(
            'SELECT uuid FROM log WHERE message = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['uuid test']);

        /** @var array{uuid: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertSame('test-uuid-value', $row['uuid']);
    }

    #[Test]
    public function writePopulatesUserIdentifierWhenPresent(): void
    {
        $handler = new PhpDbHandler($this->adapter, 'log');
        $record  = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'security',
            level: Level::Warning,
            message: 'user identifier test',
            formatted: 'user identifier test',
            extra: ['email' => 'user@example.com'],
        );

        $handler->handle($record);

        $stmt = $this->pdo->prepare(
            'SELECT user_identifier FROM log WHERE message = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['user identifier test']);

        /** @var array{user_identifier: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertSame('user@example.com', $row['user_identifier']);
    }

    #[Test]
    public function writeSerializesContextToJson(): void
    {
        $handler = new PhpDbHandler($this->adapter, 'log');
        $record  = new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'app',
            level: Level::Error,
            message: 'context test',
            formatted: 'context test',
            context: ['key' => 'value'],
        );

        $handler->handle($record);

        $stmt = $this->pdo->prepare(
            'SELECT context FROM log WHERE message = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['context test']);

        /** @var array{context: string}|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertStringContainsString('"key"', $row['context']);
        $this->assertStringContainsString('"value"', $row['context']);
    }

    private function makeRecord(string $message = 'test message'): LogRecord
    {
        return new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'app',
            level: Level::Info,
            message: $message,
            formatted: $message,
        );
    }
}
