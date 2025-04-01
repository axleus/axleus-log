<?php

declare(strict_types=1);

namespace Axleus\Log;

enum Runtime: string
{
    case Mezzio = 'mezzio';
    case Mvc    = 'mvc';
}
