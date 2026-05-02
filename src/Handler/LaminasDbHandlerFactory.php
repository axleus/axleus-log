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

use Laminas\Db\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class LaminasDbHandlerFactory
{
    public function __invoke(ContainerInterface $container): LaminasDbHandler
    {
        $config = $container->get('config');
        if (! empty($config[LoggerInterface::class])) {
            $config = $config[LoggerInterface::class];
        }

        // laminas-db registers its adapter under Laminas\Db\Adapter\AdapterInterface::class
        /** @var AdapterInterface */
        $adapter = $container->get(AdapterInterface::class);

        return new LaminasDbHandler(
            $adapter,
            $config['table'],
            $container->get('config')['authentication']['username'] ?? 'email'
        );
    }
}
