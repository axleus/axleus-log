<?php

declare(strict_types=1);

namespace AxleusTestIntegration\Log\Extension;

use AxleusTest\Log\Platform\FixtureLoader;
use AxleusTest\Log\Platform\MysqlFixtureLoader;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;

use function getenv;

final class IntegrationTestStartedListener implements StartedSubscriber
{
    /** @var FixtureLoader[] */
    private $fixtureLoaders = [];

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
