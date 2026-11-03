<?php

declare(strict_types=1);

function g_esc(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function g_config(): array
{
    static $config;
    if ($config !== null) {
        return $config;
    }

    $path = dirname(__DIR__).'/config.php';
    $sample = dirname(__DIR__).'/config.sample.php';
    $config = is_file($path) ? require $path : (is_file($sample) ? require $sample : []);

    return is_array($config) ? $config : [];
}

function g_base_path(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // When using router.php, SCRIPT_NAME is /router.php — treat as root.
    if (str_ends_with($script, '/router.php') || $script === '/router.php') {
        return '';
    }

    $dir = str_replace('\\', '/', dirname($script));
    if ($dir === '/' || $dir === '.' || $dir === '') {
        return '';
    }

    // Strip /bg or page folders for asset base — pages pass $base explicitly.
    return rtrim($dir, '/');
}
