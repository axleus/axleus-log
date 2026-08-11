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

namespace Webware\Log\Listener;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class MezzioErrorListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(
        Throwable $e,
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): void {
        $this->logger->error($e->getMessage(), [
            'exception' => $e,
            'request' => $request,
            'response' => $response,
        ]);
    }
}
