<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Axleus\Log\ConfigProvider;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class LaminasDbHandlerFactory
{
    public function __invoke(ContainerInterface $container): LaminasDbHandler
    {
        /** @var array $config */
        $config = $container->get('config');
        if (
            ! empty($config[ConfigProvider::class])
        ) {
            $config = $config[ConfigProvider::class];
        }
        $adapter = $container->get(AdapterInterface::class);

        return new LaminasDbHandler(
            $adapter,
            $config['table'],
            $container->get('config')['authentication']['username'] ?? 'email'
        );
    }
}
