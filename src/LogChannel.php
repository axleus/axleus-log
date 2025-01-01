<?php

declare(strict_types=1);

namespace Axleus\Log;

enum LogChannel: string
{
    case App   = 'app';
    case Error = 'error';
    case User  = 'user';
}
