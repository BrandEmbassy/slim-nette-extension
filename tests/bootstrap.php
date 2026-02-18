<?php declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('apcu_enabled')) {
    function apcu_enabled(): bool
    {
        return false;
    }
}
