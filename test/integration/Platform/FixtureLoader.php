<?php

declare(strict_types=1);

namespace AxleusTestIntegration\Log\Platform;

// phpcs:ignore WebimpressCodingStandard.NamingConventions.Interface.Suffix
interface FixtureLoader
{
    public function createDatabase(): void;

    public function dropDatabase(): void;
}
