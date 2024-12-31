<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Axleus\Db;
use Axleus\Log\ConfigProvider;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class LaminasDbHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LaminasDbHandler
    {
        /** @var array{log: array{table: string}} */
        $config = $container->get('config');
        if (! empty($config[ConfigProvider::class])
        ) {
            $config = $config[ConfigProvider::class];
        }
        /** @var Adapter */
        $adapter = $container->get(AdapterInterface::class);

        //$table = $config['log']['table'];
        return new LaminasDbHandler(
            new TableGateway(
                new Db\Sql\TableIdentifier(
                    $config['table'],
                    $config['table_prefix']
                ),
                $adapter
            )
        );
    }
}
