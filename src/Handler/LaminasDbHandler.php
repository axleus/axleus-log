<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Laminas\Db\TableGateway\TableGatewayInterface;
use Monolog\Level;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

// todo: refactor this to use the adapter and a insert instance
final class LaminasDbHandler extends AbstractProcessingHandler
{
    public function __construct(
        private TableGatewayInterface $gateway,
        protected Level $level = Level::Debug,
        protected bool $bubble = true
    ) {
    }

    /**
     *
     * @param LogRecord $record
     * @return void
     * @psalm-suppress all
     */
    protected function write(LogRecord $record): void
    {
        $message = [
            'channel' => $record['channel'],
            'level'   => $record['level_name'],
            'uuid'    => $record['extra']['uuid'] ?? null,
            'message' => $record->formatted,
            'time'    => $record->datetime->format('U'),
            'email'   => $record['extra']['email'] ?? null,
        ];
        // todo wrap this in a try catch
        $this->gateway->insert($message);
    }
}
