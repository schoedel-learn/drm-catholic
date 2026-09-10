<?php

declare(strict_types=1);

/**
 * Deterministic, offline HTTP fixture that mimics the deployed DRM demo
 * contract so tests can exercise scripts/verify-demo.zsh against real HTTP
 * responses instead of asserting on the script's source text.
 *
 * Run as the router for the PHP built-in server:
 *   php -S 127.0.0.1:PORT tests/Fixtures/demo-verify-server.php
 *
 * The response shape is controlled entirely by environment variables so a
 * single fixture can reproduce every success and failure path:
 *   FIXTURE_GIT_SHA   The SHA reported by /api/v1/health (default "deadbeef").
 *   FIXTURE_SCENARIO  One of:
 *       ok                Every check passes.
 *       bad-sha           /api/v1/health reports a different SHA.
 *       no-noindex        Browser responses omit the demo noindex header.
 *       register-enabled  /register answers 200 instead of 404.
 *       forgot-enabled    /forgot-password answers 200 instead of 404.
 *       health-503        /api/v1/health reports 503 unavailable.
 *       down              /up never becomes ready (returns 500).
 */
$scenario = getenv('FIXTURE_SCENARIO') ?: 'ok';
$sha = getenv('FIXTURE_GIT_SHA') ?: 'deadbeef';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isApi = str_starts_with($path, '/api/');

// The application attaches the demo noindex header to browser (non-API)
// responses only; reproduce that here so the header assertions are genuine.
if (! $isApi && $scenario !== 'no-noindex') {
    header('X-Robots-Tag: noindex, nofollow');
}

$json = static function (int $status, array $payload): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_THROW_ON_ERROR);
};

switch ($path) {
    case '/up':
        if ($scenario === 'down') {
            http_response_code(500);
            echo 'unavailable';
            break;
        }

        http_response_code(200);
        echo 'OK';
        break;

    case '/':
    case '/login':
        http_response_code(200);
        echo '<!doctype html><title>DRM demo</title>';
        break;

    case '/api/v1/health':
        if ($scenario === 'health-503') {
            $json(503, ['status' => 'unavailable', 'database' => 'error', 'git_sha' => $sha]);
            break;
        }

        $reported = $scenario === 'bad-sha' ? 'unexpected-deployment-sha' : $sha;
        $json(200, ['status' => 'ok', 'database' => 'ok', 'git_sha' => $reported]);
        break;

    case '/register':
        if ($scenario === 'register-enabled') {
            http_response_code(200);
            echo 'registration form';
            break;
        }

        http_response_code(404);
        echo 'Not Found';
        break;

    case '/forgot-password':
        if ($scenario === 'forgot-enabled') {
            http_response_code(200);
            echo 'password reset form';
            break;
        }

        http_response_code(404);
        echo 'Not Found';
        break;

    default:
        http_response_code(404);
        echo 'Not Found';
}
