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

class MonologMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface|Logger $logger
    ) {
    }

    /**
     * @psalm-suppress all
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        // todo: abstract this to detect which config is being used, mezzio-authentication or axleus-usermanager
        /** @var UserInterface */
        $userInterface = $request->getAttribute(UserInterface::class);

        $this->logger->pushProcessor(function (LogRecord $record) use ($userInterface) {
            /** @var non-empty-string */
            $record['extra']['email'] = $userInterface->getIdentity();
            return $record;
        });
        return $handler->handle($request);
    }
}
