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
        /** @var array{log: array{table: string}} */
        $config = $container->get('config');
        if (! empty($config[LoggerInterface::class])) {
            $config = $config[LoggerInterface::class];
        }
        $channel = LogChannel::tryFrom($config['channel']);
        $logger  = new Logger($channel->value);

        /** @var LaminasDbHandler */
        $laminasDbHandler = $container->get(LaminasDbHandler::class);
        $logger->pushHandler($laminasDbHandler);
        $processor = new Processor\RamseyUuidProcessor();
        $logger->pushProcessor($processor);
        $processor = new PsrLogMessageProcessor(null, false);
        $logger->pushProcessor($processor);
        if ($container->has(TranslatorInterface::class)) {
            $logger->pushProcessor($container->get(Processor\LaminasI18nProcessor::class));
        }

        return $logger;
    }
}
