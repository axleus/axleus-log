<?php

declare(strict_types=1);

namespace Axleus\Log;

use Axleus\SettingsProvider as Provider;

final class SettingsProvider extends Provider
{
    public const SETTINGS_FILE = 'log.php';

    protected ?string $file = self::SETTINGS_FILE;

    public function __invoke(): array
    {
        return parent::__invoke();
    }
}
