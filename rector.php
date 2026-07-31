<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
    ])
    ->withRules([
        NewlineAfterStatementRector::class,
    ])
    ->withSets([

    ])
    ->withImportNames(importDocBlockNames: false, importShortClasses: false)
    ->withPhpSets(php85: true)
    ->withAttributesSets(symfony: true, doctrine: true, sensiolabs: true)
    ->withComposerBased(symfony: true);
