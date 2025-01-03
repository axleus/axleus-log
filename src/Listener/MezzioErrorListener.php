<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Axleus\Log\LogChannel;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class MezzioErrorListener
{
    public function __construct(private LoggerInterface $logger) {}

    public function __invoke(
        Throwable $e,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): void {
        $this->logger->error($e->getMessage(), [
            'exception' => $e,
            'request'   => $request,
            'response'  => $response,
        ]);
    }
}
