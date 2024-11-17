<?php

declare(strict_types=1);

namespace Axleus\Log\Container;

use Axleus\Log\ConfigProvider;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class LogFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        /** @var array{log: array{table: string}} */
        $config = $container->get('config');
        if (
            ! empty($config[ConfigProvider::APP_SETTINGS_KEY])
            && ! empty($config[ConfigProvider::APP_SETTINGS_KEY][ConfigProvider::class])
        ) {
            $config = $config[ConfigProvider::APP_SETTINGS_KEY][ConfigProvider::class];
        }

        $logger = new Logger($config['channel']);
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
