<?php

declare(strict_types=1);

namespace Axleus\Log\Container;

use Axleus\Log\ConfigProvider;
use Axleus\Log\Handler\LaminasDbHandler;
use Axleus\Log\LogChannel;
use Axleus\Log\Processor;
use Laminas\Translator\TranslatorInterface;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class LogFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        /** @var array $config*/
        $config = $container->get('config');
        if (! empty($config[ConfigProvider::class])) {
            $config = $config[ConfigProvider::class];
        }

        if (isset($config['channel'])) {
            $channel = LogChannel::tryFrom($config['channel'])->value;
        } else {
            $channel = LogChannel::App->value;
        }

        $logger = new Logger($channel);
        $laminasDbHandler = $container->get(LaminasDbHandler::class);
        $logger->pushHandler($laminasDbHandler);

        if ($config['enable_uuid']) {
            $logger->pushProcessor(new Processor\RamseyUuidProcessor());
        }

        $processor = new PsrLogMessageProcessor(null, false);
        $logger->pushProcessor($processor);

        if ($config['enable_translatator'] && $container->has(TranslatorInterface::class)) {
            $logger->pushProcessor($container->get(Processor\LaminasI18nProcessor::class));
        }

        return $logger;
    }
}
