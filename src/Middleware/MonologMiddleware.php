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

namespace Axleus\Log\Middleware;

use Mezzio\Authentication\UserInterface;
use Monolog\Logger;
use Monolog\LogRecord;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class MonologMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface|Logger $logger,
    ) {}

    /**
     * @psalm-suppress all
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // todo: abstract this to detect which config is being used, mezzio-authentication or axleus-usermanager
        /** @var UserInterface */
        $userInterface = $request->getAttribute(UserInterface::class);

        $this->logger->pushProcessor(function (LogRecord $record) use ($userInterface) {
            /** @var non-empty-string */
            $record['extra']['email'] = $userInterface?->getIdentity();

            return $record;
        });

        // attach the logger to the request
        $request = $request->withAttribute(LoggerInterface::class, $this->logger);

        return $handler->handle($request);
    }
}
