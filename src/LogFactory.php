<?php

declare(strict_types=1);

namespace Axleus\Log;

use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class LogFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        $logger = new Logger('app');
        /** @var RepositoryHandler */
        $repoHandler = $container->get(RepositoryHandler::class);
        $logger->pushHandler($repoHandler);
        $processor = new Processor\RamseyUuidProcessor();
        $logger->pushProcessor($processor);
        $processor = new PsrLogMessageProcessor(null, false);
        $logger->pushProcessor($processor);
        $logger->pushProcessor($container->get(Processor\LaminasI18nProcessor::class));

        return $logger;
    }
}
