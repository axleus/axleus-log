<?php

declare(strict_types=1);

namespace Axleus\Log;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Log\LoggerInterface;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'runtime'      => Runtime::Mezzio,
            'dependencies' => $this->getDependencies(),
            'listeners'    => $this->getListeners(),
            //'middleware_pipeline' => $this->getPipelineConfig(),
            'templates'   => $this->getTemplates(),
            static::class => $this->getAxleusConfig(),
        ];
    }

    public function getAxleusConfig(): array
    {
        return [
            'log_errors'        => false,
            'channel'           => 'app',
            'table'             => 'log',
            'enable_auth'       => false,
            'enable_uuid'       => false,
            'enable_translator' => false,
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

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'log' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
