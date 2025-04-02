<?php

declare(strict_types=1);

namespace Axleus\Log\Middleware;

use Monolog\Logger;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;

final class MonologMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface|Logger $logger
    ) {
    }
}
