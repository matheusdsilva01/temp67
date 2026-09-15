<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/bootstrap/providers.php',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/public',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withImportNames(importShortClasses: false)
    ->withBootstrapFiles([__DIR__.'/vendor/larastan/larastan/bootstrap.php'])
    ->withPHPStanConfigs([__DIR__.'/phpstan.neon'])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        rectorPreset: true,
    )
    ->withPhpSets()
    ->withSkip([
        AddArrowFunctionReturnTypeRector::class,
        DeclareStrictTypesRector::class,
        PostIncDecToPreIncDecRector::class,
    ]);
