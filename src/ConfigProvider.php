<?php

declare(strict_types=1);

namespace Axleus\Log;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'        => $this->getDependencies(),
            'listeners'           => $this->getListeners(),
            'log_runtime'         => Runtime::Mezzio->value,
            //'middleware_pipeline' => $this->getPipelineConfig(),
            'templates'           => $this->getTemplates(),
            static::class         => $this->getAxleusConfig(),
        ];
    }

    public function getAxleusConfig(): array
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
                Listener\MvcErrorListener::class      => Listener\MvcErrorListenerFactory::class,
                Listener\Psr3LogListener::class       => Listener\Psr3LogListenerFactory::class,
                LoggerInterface::class                => Container\LogFactory::class,
                Middleware\MonologMiddleware::class   => Middleware\MonologMiddlewareFactory::class,
                Handler\LaminasDbHandler::class       => Handler\LaminasDbHandlerFactory::class,
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
            Listener\Psr3LogListener::class,
        ];
    }

    public function getPipelineConfig(): array
    {
        return [
            [
                'middleware' => [
                    Middleware\MonologMiddleware::class,
                ],
                //'priority'   => 9000,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'log'    => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
