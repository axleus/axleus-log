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

namespace Axleus\Log\Listener;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class Psr3LogLaminasListenerFactory
{
    public function __invoke(ContainerInterface $container): Psr3LogLaminasListener
    {
        return new Psr3LogLaminasListener(
            $container->get(LoggerInterface::class)
        );
    }
}
