<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

use Axleus\Log\LogChannel;
use Laminas\Authentication\AuthenticationService;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;

final class MvcErrorListener extends AbstractListenerAggregate
{
    public function __construct(
        private LoggerInterface $logger,
        private array $config,
        private ?AuthenticationService $auth = null
    ) {
    }
}
