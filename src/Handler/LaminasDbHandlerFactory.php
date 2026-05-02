<?php

declare(strict_types=1);

/**
 * This file is part of the Axleus Log package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axleus\Log\Handler;

use Axleus\Log\ConfigProvider;
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

        // $table = $config['log']['table'];
        return new LaminasDbHandler(
            $adapter,
            $config['table'],
            $container->get('config')['authentication']['username'] ?? 'email' // support mezzio-authentication-session
        );
    }
}
