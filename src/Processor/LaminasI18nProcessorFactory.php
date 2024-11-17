<?php

declare(strict_types=1);

namespace Axleus\Log\Processor;

use Laminas\I18n\Translator\TranslatorInterface;
use Monolog\Processor\ProcessorInterface;
use Psr\Container\ContainerInterface;

final class LaminasI18nProcessorFactory
{
    public function __invoke(ContainerInterface $container): ProcessorInterface
    {
        /** @var LaminasI18nProcessor*/
        $processor = new LaminasI18nProcessor();
        if ($container->has(TranslatorInterface::class)) {
            $processor->setTranslator($container->get(TranslatorInterface::class));
        }
        return $processor;
    }
}
