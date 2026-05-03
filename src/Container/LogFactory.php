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

/**
 * @phpstan-import-type LogDefaults from ConfigProvider
 */
final class LogFactory
{
    public function __invoke(ContainerInterface $container): LoggerInterface
    {
        /** @var array{LoggerInterface::class?: LogDefaults}&array<string, mixed> */
        $rawConfig = $container->get('config');

        /** @var LogDefaults $config */
        $config = ! empty($rawConfig[LoggerInterface::class])
            ? $rawConfig[LoggerInterface::class]
            : (new ConfigProvider())->getConfigDefaults();
        $channel = LogChannel::tryFrom($config['channel']) ?? LogChannel::App;
        $logger  = new Logger($channel->value);

        /** @var LaminasDbHandler */
        $laminasDbHandler = $container->get(LaminasDbHandler::class);
        $logger->pushHandler($laminasDbHandler);
        if ($config['process_uuid'] ?? false) {
            $processor = new Processor\RamseyUuidProcessor();
            $logger->pushProcessor($processor);
        }
        $processor = new PsrLogMessageProcessor(null, false);
        $logger->pushProcessor($processor);
        if (($config['process_translation'] ?? false) && $container->has(TranslatorInterface::class)) {
            /** @var Processor\LaminasI18nProcessor $i18nProcessor */
            $i18nProcessor = $container->get(Processor\LaminasI18nProcessor::class);
            $logger->pushProcessor($i18nProcessor);
        }

        return $logger;
    }
}
