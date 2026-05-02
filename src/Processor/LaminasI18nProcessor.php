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
