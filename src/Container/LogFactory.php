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
    /**
     * @var array{
     *  channel: string,
     *  enable_uuid: bool,
     *  enable_translatator: bool
     * } $config
     */
    private array $config;

    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        /** @var array $config*/
        $config = $container->get('config');
        if (! empty($config[ConfigProvider::class])) {
            $this->config = $config[ConfigProvider::class];
        }

        if (isset($config['channel'])) {
            $channel = LogChannel::tryFrom($this->config['channel'])->value;
        } else {
            $channel = LogChannel::App->value;
        }

        $logger           = new Logger($channel);
        $laminasDbHandler = $container->get(LaminasDbHandler::class);
        $logger->pushHandler($laminasDbHandler);

        if ($this->config['enable_uuid']) {
            $logger->pushProcessor(new Processor\RamseyUuidProcessor());
        }

        $processor = new PsrLogMessageProcessor(null, false);
        $logger->pushProcessor($processor);

        if ($this->config['enable_translatator'] && $container->has(TranslatorInterface::class)) {
            $logger->pushProcessor($container->get(Processor\LaminasI18nProcessor::class));
        }

        return $logger;
    }
}
