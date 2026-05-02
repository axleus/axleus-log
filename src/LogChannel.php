<?php

declare(strict_types=1);

namespace Axleus\Log;

enum LogChannel: string
{
    case Audit     = 'audit';
    case Analytics = 'analytics';
    case App       = 'app';
    case Debug     = 'debug';
    case Error     = 'error';
    case System    = 'system';
    case User      = 'user';
    case Security  = 'security';
}
