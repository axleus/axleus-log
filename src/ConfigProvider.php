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
use Phly\EventDispatcher\EventDispatcher;
use Phly\EventDispatcher\ListenerProvider\AttachableListenerProvider;
use Phly\EventDispatcher\ListenerProvider\ListenerProviderAggregate;
use Phly\EventDispatcher\ListenerProvider\PrioritizedListenerProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public const LISTENER_KEY          = 'listeners';

    public const LISTENER_PROVIDER_KEY = 'listener_providers';

    public function __invoke(): array
    {
        return [
            'dependencies'         => $this->getDependencies(),
            self::LISTENER_KEY     => $this->getListeners(),
            self::LISTENER_PROVIDER_KEY => [],
            // 'middleware_pipeline' => $this->getPipelineConfig(),
            'templates'            => $this->getTemplates(),
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
            'aliases'    => [
                EventDispatcherInterface::class  => EventDispatcher::class,
                ListenerProviderInterface::class => ListenerProviderAggregate::class,
            ],
            'delegators' => [
                ErrorHandler::class => [
                    Container\MezzioErrorHandlerDelegator::class,
                ],
            ],
            'factories'  => [
                ListenerProviderAggregate::class             => Container\ListenerProviderAggregateFactory::class,
                Listener\Psr3LogLaminasListener::class       => Listener\Psr3LogLaminasListenerFactory::class,
                Listener\Psr3LogPsr14Listener::class         => Listener\Psr3LogPsr14ListenerFactory::class,
                LoggerInterface::class                       => Container\LogFactory::class,
                Middleware\MonologMiddleware::class           => Middleware\MonologMiddlewareFactory::class,
                Handler\LaminasDbHandler::class              => Handler\LaminasDbHandlerFactory::class,
                Handler\PhpDbHandler::class                  => Handler\PhpDbHandlerFactory::class,
                Processor\LaminasI18nProcessor::class        => Processor\LaminasI18nProcessorFactory::class,
            ],
            'invokables' => [
                AttachableListenerProvider::class    => AttachableListenerProvider::class,
                PrioritizedListenerProvider::class   => PrioritizedListenerProvider::class,
                Processor\RamseyUuidProcessor::class => Processor\RamseyUuidProcessor::class,
            ],
        ];
    }

    public function getListeners(): array
    {
        return [
            Event\LogEvent::class => [
                ['listener' => Listener\Psr3LogPsr14Listener::class, 'priority' => 1],
            ],
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
