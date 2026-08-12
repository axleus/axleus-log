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

namespace WebwareTestIntegration\Log\FixtureLoader;

use Exception;
use Override;
use PDO;
use PDOException;

use function assert;
use function file_get_contents;
use function getenv;
use function print_r;
use function sprintf;

final class MysqlFixtureLoader implements FixtureLoaderInterface
{
    private string $fixtureFile = __DIR__ . '/../TestFixtures/mysql.sql';

    private ?PDO $pdo = null;

    /**
     * @throws Exception
     */
    #[Override]
    public function createDatabase(): void
    {
        $this->connect();
        assert(
            assertion: $this->pdo instanceof PDO,
            description: 'PDO connection is not established',
        );
        if (
            false === $this->pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS %s',
                getenv('TESTS_ADAPTER_MYSQL_DATABASE'),
            ))
        ) {
            throw new Exception(sprintf(
                'I cannot create the MySQL %s test database: %s',
                getenv('TESTS_ADAPTER_MYSQL_DATABASE'),
                print_r($this->pdo->errorInfo(), true),
            ));
        }

        $this->pdo->exec('USE ' . getenv('TESTS_ADAPTER_MYSQL_DATABASE'));

        $sql = file_get_contents($this->fixtureFile);
        if (false === $sql || false === $this->pdo->exec($sql)) {
            throw new Exception(sprintf(
                'I cannot create the table for %s database. Check the %s file. %s ',
                getenv('TESTS_ADAPTER_MYSQL_DATABASE'),
                $this->fixtureFile,
                print_r($this->pdo->errorInfo(), true),
            ));
        }

        $this->disconnect();
    }

    /**
     * @throws PDOException
     */
    #[Override]
    public function dropDatabase(): void
    {
        $this->connect();
        assert($this->pdo instanceof PDO);

        $this->pdo->exec(sprintf(
            'DROP DATABASE IF EXISTS %s',
            getenv('TESTS_ADAPTER_MYSQL_DATABASE'),
        ));

        $this->disconnect();
    }

    /**
     * @throws PDOException
     */
    protected function connect(): void
    {
        $dsn = 'mysql:host=' . getenv('TESTS_ADAPTER_MYSQL_HOSTNAME');
        if (getenv('TESTS_ADAPTER_MYSQL_PORT')) {
            $dsn .= ';port=' . getenv('TESTS_ADAPTER_MYSQL_PORT');
        }

        $this->pdo = new PDO(
            $dsn,
            getenv('TESTS_ADAPTER_MYSQL_USERNAME') ?: null,
            getenv('TESTS_ADAPTER_MYSQL_PASSWORD') ?: null,
        );
    }

    protected function disconnect(): void
    {
        $this->pdo = null;
    }
}
