<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Axleus\Log\ConfigProvider;
use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class LaminasDbHandlerFactory
{
    /**
     * @var array{table: string} $config
     */
    private array $config;

    public function __invoke(ContainerInterface $container): LaminasDbHandler
    {
        /** @var array $config */
        $config = $container->get('config');
        if (
            ! empty($config[ConfigProvider::class])
        ) {
            $this->config = $config[ConfigProvider::class];
        }
        $adapter = $container->get(AdapterInterface::class);

        return new LaminasDbHandler(
            $adapter,
            $this->config['table'],
            $container->get('config')['authentication']['username'] ?? 'email'
        );
    }
}
