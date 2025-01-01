<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

// todo: refactor this to use the adapter and a insert instance
final class LaminasDbHandler extends AbstractProcessingHandler
{
    public function __construct(
        private AdapterInterface $adapterInterface,
        private string $table,
        private string $extraAuthIdentifier = 'email',
        protected bool $bubble = true
    ) {
    }

    protected function write(LogRecord $record): void
    {
        $message = [
            'channel'        => $record['channel'],
            'level'          => $record['level_name'],
            'uuid'           => $record['extra']['uuid'] ?? null,
            'message'        => $record->formatted,
            'time'           => $record->datetime->format('U'),
            'userIdentifier' => $record['extra'][$this->extraAuthIdentifier] ?? null, // 'email' needs to change to userId
        ];
        $sql    = new Sql($this->adapterInterface, $this->table);
        $insert = $sql->insert();
        $insert->values($message);
        $result = $sql->prepareStatementForSqlObject($insert)->execute();
    }
}
