<?php

declare(strict_types=1);

namespace Axleus\Log\Processor;

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorAwareTrait;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class LaminasI18nProcessor implements ProcessorInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function __invoke(LogRecord $record): LogRecord
    {
        $translator = $this->getTranslator();
        $translated = $translator->translate($record->message);
        return $record->with(message: $translated, context: $record->context, extra: $record->extra);
    }
}
