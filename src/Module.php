<?php

declare(strict_types=1);

namespace Axleus\Log;

final class Module
{
    public function getConfig(): array
    {
        $provider = new ConfigProvider();
        return [
            ConfigProvider::class => $provider->getAxleusConfig(),
            'service_manager'     => $provider->getDependencies(),
            'listeners' => [
                Listener\MvcErrorListener::class,
                Listener\Psr3LogListener::class,
            ],
        ];
    }
}
