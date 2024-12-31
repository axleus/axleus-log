<?php
// todo: migrate this module to axleus/axleus-core
declare(strict_types=1);

namespace Axleus\Log;

use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'           => $this->getDependencies(),
            //'middleware_pipeline'    => $this->getPipelineConfig(),
            'templates'              => $this->getTemplates(),
            static::class            => $this->getAxleusConfig(),
        ];
    }

    public function getAxleusConfig(): array
    {
        return [
            'channel'      => 'app',
            'table'        => 'log',
            'table_prefix' => null,
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories'  => [
                Listener\MvcErrorListener::class      => Listener\MvcErrorListenerFactory::class,
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

    public function getPipelineConfig(): array
    {
        return [
            [
                'middleware' => [
                    Middleware\MonologMiddleware::class,
                ],
                'priority'   => 9000,
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
