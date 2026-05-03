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

namespace AxleusTestIntegration\Log\Extension;

use AxleusTestIntegration\Log\FixtureLoader\MysqlFixtureLoader;
use PHPUnit\Event\TestSuite\Finished;
use PHPUnit\Event\TestSuite\FinishedSubscriber;

final class IntegrationTestStoppedListener implements FinishedSubscriber
{
    /** @var list<MysqlFixtureLoader> */
    private array $fixtureLoaders = [];

    public function notify(Finished $event): void
    {
        if (
            $event->testSuite()->name() !== 'integration test'
            || empty($this->fixtureLoaders)
        ) {
            return;
        }

        print "\nIntegration test ended.\n";

        foreach ($this->fixtureLoaders as $fixtureLoader) {
            $fixtureLoader->dropDatabase();
        }
    }
}
