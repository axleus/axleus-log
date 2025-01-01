<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Axleus\Log\ConfigProvider;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class LaminasDbHandlerFactory
{
    public function __invoke(ContainerInterface $container): LaminasDbHandler
    {
        /** @var array{log: array{table: string}} */
        $config = $container->get('config');
        if (! empty($config[ConfigProvider::class])
        ) {
            $config = $config[ConfigProvider::class];
        }
        /** @var AdapterInterface */
        $adapter = $container->get(AdapterInterface::class);

        //$table = $config['log']['table'];
        return new LaminasDbHandler(
            $adapter,
            $config['table'],
            $container->get('config')['authentication']['username'] ?? 'email' // support mezzio-authentication-session
        );
    }
}
