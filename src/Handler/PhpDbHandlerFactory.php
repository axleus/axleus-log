<?php

declare(strict_types=1);

namespace Axleus\Log\Handler;

use Axleus\Log\ConfigProvider;
use PhpDb\Adapter\AdapterInterface;
use Psr\Container\ContainerInterface;

final class PhpDbHandlerFactory
{
    public function __invoke(ContainerInterface $container): PhpDbHandler
    {
        $config = $container->get('config');
        if (! empty($config[ConfigProvider::class])) {
            $config = $config[ConfigProvider::class];
        }
        /** @var AdapterInterface */
        $adapter = $container->get(AdapterInterface::class);

        return new PhpDbHandler(
            $adapter,
            $config['table'],
            $container->get('config')['authentication']['username'] ?? 'email'
        );
    }
}
