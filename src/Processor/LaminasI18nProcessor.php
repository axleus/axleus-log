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
use Laminas\Translator\TranslatorInterface;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class LaminasI18nProcessor implements ProcessorInterface, TranslatorAwareInterface
{
    protected ?TranslatorInterface $translator       = null;
    protected bool $translatorEnabled                = true;
    protected string $translatorTextDomain           = 'default';

    public function setTranslator(?TranslatorInterface $translator = null, $textDomain = null): static
    {
        $this->translator = $translator;

        if ($textDomain !== null) {
            $this->setTranslatorTextDomain($textDomain);
        }

        return $this;
    }

    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function hasTranslator(): bool
    {
        return $this->translator !== null;
    }

    public function setTranslatorEnabled($enabled = true): static
    {
        $this->translatorEnabled = $enabled;

        return $this;
    }

    public function isTranslatorEnabled(): bool
    {
        return $this->translatorEnabled;
    }

    public function setTranslatorTextDomain($textDomain = 'default'): static
    {
        $this->translatorTextDomain = $textDomain;

        return $this;
    }

    public function getTranslatorTextDomain(): string
    {
        return $this->translatorTextDomain;
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $translator = $this->getTranslator();
        if ($translator === null) {
            return $record;
        }

        $translated = $translator->translate($record->message);

        return $record->with(message: $translated, context: $record->context, extra: $record->extra);
    }
}
