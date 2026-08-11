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

namespace Webware\Log\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Uuid;
use Override;

final class RamseyUuidProcessor implements ProcessorInterface
{
    /**
     * @throws UnsupportedOperationException
     */
    #[Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['uuid'] = Uuid::uuid7($record->datetime)->toString();

        return $record;
    }
}
