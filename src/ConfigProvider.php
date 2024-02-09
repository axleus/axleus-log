<?php
// todo: migrate this module to axleus/axleus-core
declare(strict_types=1);

namespace Axleus\Log;

use Psr\Log\LoggerInterface;

/**
 * The configuration provider for the Log module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'middleware_pipeline' => $this->getPipelineConfig(),
            'templates'    => $this->getTemplates(),
            SettingsProvider::class => (new SettingsProvider)(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Processor\RamseyUuidProcessor::class => Processor\RamseyUuidProcessor::class,
            ],
            'factories'  => [
                LoggerInterface::class   => LogFactory::class,
                MonologMiddleware::class => MonologMiddlewareFactory::class,
                RepositoryHandler::class => RepositoryHandlerFactory::class,
                Processor\LaminasI18nProcessor::class => Processor\LaminasI18nProcessorFactory::class,
            ],
        ];
    }

    public function getPipelineConfig(): array
    {
        return [
            [
                'middleware' => [
                    MonologMiddleware::class,
                ],
                'priority'   => 9000,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'log'    => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
