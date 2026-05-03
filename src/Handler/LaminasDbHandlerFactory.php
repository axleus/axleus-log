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
use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type LogDefaults from ConfigProvider
 */
final class LaminasDbHandlerFactory
{
    public function __invoke(ContainerInterface $container): LaminasDbHandler
    {
        /** @var array{LoggerInterface::class?: LogDefaults, authentication?: array{username?: string}}&array<string, mixed> */
        $rawConfig = $container->get('config');

        /** @var LogDefaults $config */
        $config = ! empty($rawConfig[LoggerInterface::class])
            ? $rawConfig[LoggerInterface::class]
            : (new ConfigProvider())->getConfigDefaults();

        // laminas-db registers its adapter under Laminas\Db\Adapter\AdapterInterface::class
        /** @var AdapterInterface */
        $adapter = $container->get(AdapterInterface::class);

        return new LaminasDbHandler(
            $adapter,
            $config['table'],
            $rawConfig['authentication']['username'] ?? 'email'
        );
    }
}
