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

namespace Axleus\Log;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'       => $this->getDependencies(),
            'listeners'          => $this->getListeners(),
            // 'middleware_pipeline' => $this->getPipelineConfig(),
            'templates'          => $this->getTemplates(),
            LoggerInterface::class => $this->getConfigDefaults(),
        ];
    }

    public function getConfigDefaults(): array
    {
        return [
            'channel'             => LogChannel::App->value,
            'log_errors'          => false,
            'process_uuid'        => false,
            'process_translation' => false,
            'table'               => 'log',
        ];
    }

    public function getDependencies(): array
    {
        return [
            'delegators' => [
                ErrorHandler::class => [
                    Container\MezzioErrorHandlerDelegator::class,
                ],
            ],
            'factories'  => [
                Listener\Psr3LogLaminasListener::class       => Listener\Psr3LogLaminasListenerFactory::class,
                LoggerInterface::class                       => Container\LogFactory::class,
                Middleware\MonologMiddleware::class   => Middleware\MonologMiddlewareFactory::class,
                Handler\LaminasDbHandler::class       => Handler\LaminasDbHandlerFactory::class,
                Handler\PhpDbHandler::class           => Handler\PhpDbHandlerFactory::class,
                Processor\LaminasI18nProcessor::class => Processor\LaminasI18nProcessorFactory::class,
            ],
            'invokables' => [
                Processor\RamseyUuidProcessor::class => Processor\RamseyUuidProcessor::class,
            ],
        ];
    }

    public function getListeners(): array
    {
        return [
            Listener\Psr3LogLaminasListener::class,
        ];
    }

    public function getPipelineConfig(): array
    {
        return [
            [
                'middleware' => [
                    Middleware\MonologMiddleware::class,
                ],
                // 'priority'   => 9000,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'log' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
