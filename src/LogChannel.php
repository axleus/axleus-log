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
