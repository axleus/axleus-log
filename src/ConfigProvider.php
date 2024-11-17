<?php
// todo: migrate this module to axleus/axleus-core
declare(strict_types=1);

namespace Axleus\Log;

use Psr\Log\LoggerInterface;

class ConfigProvider
{
    public final const APP_SETTINGS_KEY = 'app_settings';

    public function __invoke(): array
    {
        return [
            'dependencies'           => $this->getDependencies(),
            //'middleware_pipeline'    => $this->getPipelineConfig(),
            'templates'              => $this->getTemplates(),
            static::APP_SETTINGS_KEY => $this->getAppSettings(),
        ];
    }

    public function getAppSettings(): array
    {
        return [
            static::class => [
                'channel'       => 'app',
                'table'         => 'log',
                'table_prefix'  => null,
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Processor\RamseyUuidProcessor::class => Processor\RamseyUuidProcessor::class,
            ],
            'factories'  => [
                LoggerInterface::class                => Container\LogFactory::class,
                Middleware\MonologMiddleware::class   => Middleware\MonologMiddlewareFactory::class,
                Repository\RepositoryHandler::class   => Repository\RepositoryHandlerFactory::class,
                // uncomment this to provide translation support
                //Processor\LaminasI18nProcessor::class => Processor\LaminasI18nProcessorFactory::class,
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
