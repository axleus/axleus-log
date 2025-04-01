<?php

declare(strict_types=1);

namespace Axleus\Log\Middleware;

use Mezzio\Authentication\UserInterface;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class MonologMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface|Logger $logger
    ) {
    }
}
