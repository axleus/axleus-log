<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Sql\Sql;

use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;
use const JSON_UNESCAPED_SLASHES;

final class PhpDbHandler extends AbstractProcessingHandler
{
    private readonly Sql $sql;

    public function __construct(
        AdapterInterface $adapter,
        private readonly string $table,
        private readonly string $extraAuthIdentifier = 'email',
        protected bool $bubble = true
    ) {
        parent::__construct();
        $this->sql = new Sql($adapter, $this->table);
    }

    protected function write(LogRecord $record): void
    {
        $context = array_filter([
            'context' => $record->context,
            'extra'   => array_diff_key($record->extra, ['uuid' => true, $this->extraAuthIdentifier => true]),
        ]);

        $insert = $this->sql->insert()->values([
            'channel'         => $record->channel,
            'level'           => $record->level->getName(),
            'uuid'            => $record->extra['uuid'] ?? null,
            'message'         => $record->formatted,
            'time'            => $record->datetime->format('U'),
            'user_identifier' => $record->extra[$this->extraAuthIdentifier] ?? null,
            'context'         => $context !== [] ? json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
        $this->sql->prepareStatementForSqlObject($insert)->execute();
    }
}
