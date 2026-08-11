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

namespace WebwareTestIntegration\Log\Extension;

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use WebwareTestIntegration\Log\FixtureLoader\MysqlFixtureLoader;

final class IntegrationTestStartedListener implements StartedSubscriber
{
    /** @var list<MysqlFixtureLoader> */
    private array $fixtureLoaders = [];

    public function notify(Started $event): void
    {
        if ($event->testSuite()->name() !== 'integration test') {
            return;
        }

        $this->fixtureLoaders[] = new MysqlFixtureLoader();

        print "\nIntegration test started.\n";

        foreach ($this->fixtureLoaders as $fixtureLoader) {
            $fixtureLoader->createDatabase();
        }
    }
}
