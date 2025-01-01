<?php

declare(strict_types=1);

namespace Axleus\Log\Listener;

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
    ) {}

    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        if (! $this->config['log_errors']) {
            return;
        }
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, [$this, 'onBootstrap']);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onError']);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'onError']);
    }

    public function onBootstrap(MvcEvent $event): void
    {
        if ($this->auth) {
            $this->logger->pushProcessor(function (LogRecord $record) {
                /** @var non-empty-string */
                $record['extra'][$this->config['authentication']['username'] ?? 'email'] = $this->auth->getIdentity();
                return $record;
            });
        }
    }

    public function onError(MvcEvent $event): void
    {
        $exception = $event->getParam('exception');
        if ($exception) {
            $logger = $this->logger->withName('error');
            $logger->error($exception->getMessage(), ['exception' => $exception]);
        }
    }
}
