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

namespace Axleus\Log\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

// TASK-025: Sql is constructed once in the constructor to avoid re-instantiation on every write call.
final class LaminasDbHandler extends AbstractProcessingHandler
{
    private readonly Sql $sql;

    public function __construct(
        private AdapterInterface $adapterInterface,
        private string $table,
        private string $extraAuthIdentifier = 'email',
        protected bool $bubble = true,
    ) {
        $this->sql = new Sql($this->adapterInterface, $this->table);
    }

    protected function write(LogRecord $record): void
    {
        /** @var array<string, mixed> $extra */
        $extra   = $record->extra;
        $message = [
            'channel'         => $record['channel'],
            'level'           => $record['level_name'],
            'uuid'            => $extra['uuid'] ?? null,
            'message'         => $record->formatted,
            'time'            => $record->datetime->format('U'),
            // TASK-026: renamed from userIdentifier to user_identifier (snake_case)
            'user_identifier' => $extra[$this->extraAuthIdentifier] ?? null,
        ];
        $insert = $this->sql->insert();
        $insert->values($message);
        $this->sql->prepareStatementForSqlObject($insert)->execute();
    }
}
