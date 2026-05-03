<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use Webware\CodingStandard\Webware1x0Set;
use Webware\CodingStandard\WebwareCopyRight;

$composerData = json_decode(
    file_get_contents('composer.json'),
    true,
    512,
);

return (new Config())
    ->registerCustomRuleSets([
        new Webware1x0Set(),
        new WebwareCopyRight(
            packageName: 'Axleus Log',
            authorName: $composerData['authors'][0]['name'],
            authorEmail: $composerData['authors'][0]['email'],
        ),
    ])
    ->setParallelConfig(ParallelConfigFactory::detect()) // @TODO 4.0 no need to call this manually
    ->setRiskyAllowed(true)
    ->setRules([
        '@Webware/copyright-header'    => true,
        '@Webware/coding-standard-1.0' => true,
    ])
    // 💡 by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        (new Finder())
        // 💡 root folder to check
        ->in(__DIR__)
        ->notPath([
            'bin/clear-config-cache.php'
        ])
    );
