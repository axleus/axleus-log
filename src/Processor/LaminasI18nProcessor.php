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

namespace Webware\Log\Processor;

use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\I18n\Translator\TranslatorAwareTrait;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Override;

final class LaminasI18nProcessor implements ProcessorInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    #[Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $translator = $this->getTranslator();
        if (null === $translator) {
            return $record;
        }

        $translated = $translator->translate($record->message);

        return $record->with(
            message: $translated,
            context: $record->context,
            extra  : $record->extra,
        );
    }
}
