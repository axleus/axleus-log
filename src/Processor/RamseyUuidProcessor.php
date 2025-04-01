<?php

declare(strict_types=1);

namespace Axleus\Log\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Uuid;

final class RamseyUuidProcessor implements ProcessorInterface
{
    /**
     * @throws UnsupportedOperationException
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['uuid'] = (Uuid::uuid7($record->datetime))->toString();
        return $record;
    }
}
