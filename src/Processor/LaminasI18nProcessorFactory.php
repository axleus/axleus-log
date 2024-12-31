<?php

declare(strict_types=1);

namespace Axleus\Log\Processor;

use Laminas\Translator\TranslatorInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Monolog\Processor\ProcessorInterface;
use Psr\Container\ContainerInterface;

final class LaminasI18nProcessorFactory
{
    public function __invoke(ContainerInterface $container): ProcessorInterface
    {
        if (! $container->has(TranslatorInterface::class)) {
            throw new ServiceNotFoundException(TranslatorInterface::class . ' was not found in the container');
        }
        /** @var LaminasI18nProcessor*/
        $processor = new LaminasI18nProcessor();
        $processor->setTranslator($container->get(TranslatorInterface::class));
        return $processor;
    }
}
