<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

$rectorConfigBuilder = RectorConfig::configure();
$defaultRectorConfigurationSetup = require __DIR__ . '/vendor/brandembassy/coding-standard/default-rector.php';

$defaultSkipList = $defaultRectorConfigurationSetup($rectorConfigBuilder);

$rectorConfigBuilder
    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
    ->withCache(sys_get_temp_dir() . '/slim-nette-extension-rector')
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip($defaultSkipList);

return $rectorConfigBuilder;