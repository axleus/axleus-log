<?php

declare(strict_types=1);

/**
 * This file is part of the Webware Log package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webware\Log\Handler;

use PhpDb\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Webware\Log\ConfigProvider;

/**
 * @phpstan-import-type LogDefaults from ConfigProvider
 */
final class PhpDbHandlerFactory
{
    public function __invoke(ContainerInterface $container): PhpDbHandler
    {
        /** @var array{LoggerInterface::class?: LogDefaults, authentication?: array{username?: string}}&array<string, mixed> */
        $rawConfig = $container->get('config');

        /** @var LogDefaults $config */
        $config = ! empty($rawConfig[LoggerInterface::class])
            ? $rawConfig[LoggerInterface::class]
            : new ConfigProvider()->getConfigDefaults();

        // phpdb does not share laminas-db's configuration structure.
        // The adapter is wired independently by the host application under PhpDb\Adapter\AdapterInterface::class.
        /** @var AdapterInterface */
        $adapter = $container->get(AdapterInterface::class);

        return new PhpDbHandler(
            $adapter,
            $config['table'],
            $rawConfig['authentication']['username'] ?? 'email',
        );
    }
}
