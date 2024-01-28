<?php

declare(strict_types=1);

namespace Axleus\Log;

use Axleus\Db;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class RepositoryHandlerFactory
{
    /**
     *
     * @param ContainerInterface $container
     * @return RepositoryHandler
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RepositoryHandler
    {
        /** @var array{log: array{table: string}} */
        $config = $container->get('config');
        /** @var Adapter */
        $adapter = $container->get(AdapterInterface::class);

        //$table = $config['log']['table'];
        return new RepositoryHandler(
            new TableGateway(
                new Db\TableIdentifier(
                    $config[SettingsProvider::class]['table'],
                    $config[Db\SettingsProvider::class]['table_prefix']
                ),
                $adapter
            )
        );
    }
}
