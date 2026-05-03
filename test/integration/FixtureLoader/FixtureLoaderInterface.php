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

namespace AxleusTestIntegration\Log\FixtureLoader;

interface FixtureLoaderInterface
{
    public function createDatabase(): void;

    public function dropDatabase(): void;
}
