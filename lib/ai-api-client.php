<?php

declare(strict_types=1);

/**
 * Server-side client for api.goalgus.bg AI Tools API.
 * Bearer token stays in config.php — never rendered to the browser.
 */

function g_ai_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $path = dirname(__DIR__).'/config.php';
    if (! is_file($path)) {
        return [];
    }

    $loaded = require $path;

    return is_array($loaded) ? $loaded : [];
}

/**
 * @param  array<string, mixed>  $payload
 * @return array{ok: bool, status: int, body: ?array, error: ?string}
 */
function g_ai_run_tool(string $slug, array $payload): array
{
    $config = g_ai_config();
    $base = rtrim((string) ($config['api_base'] ?? 'https://api.goalgus.bg'), '/');
    $token = (string) ($config['ai_api_token'] ?? '');
    $timeout = (int) ($config['ai_request_timeout'] ?? 120);

    if ($token === '' || $token === 'PASTE_AI_API_TOKEN_HERE') {
        return [
            'ok' => false,
            'status' => 0,
            'body' => null,
            'error' => 'AI API token is not configured on this server.',
        ];
    }

    $url = $base.'/api/v1/ai/tools/'.$slug.'/run';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(15, $timeout),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$token,
            'Accept: application/json',
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]);

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if (! is_string($raw) || $raw === '') {
        return [
            'ok' => false,
            'status' => $status,
            'body' => null,
            'error' => $curlError !== '' ? $curlError : 'Empty response from AI API.',
        ];
    }

    $body = json_decode($raw, true);
    if (! is_array($body)) {
        return [
            'ok' => false,
            'status' => $status,
            'body' => null,
            'error' => 'Invalid JSON from AI API.',
        ];
    }

    return [
        'ok' => ! empty($body['ok']),
        'status' => $status,
        'body' => $body,
        'error' => empty($body['ok']) ? (string) ($body['message'] ?? 'AI run failed.') : null,
    ];
}
