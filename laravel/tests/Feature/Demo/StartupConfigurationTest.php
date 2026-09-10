<?php

namespace Tests\Feature\Demo;

use PHPUnit\Framework\TestCase;

class StartupConfigurationTest extends TestCase
{
    public function test_startup_requires_the_complete_demo_environment_contract(): void
    {
        $script = $this->contents('scripts/start-demo.sh');

        foreach (['APP_KEY', 'DEMO_USER_PASSWORD', 'DEPLOYMENT_GIT_SHA', 'DB_DATABASE'] as $variable) {
            $this->assertStringContainsString('require_variable '.$variable, $script);
        }

        $this->assertStringContainsString('DEMO_MODE" != "true"', $script);
        $this->assertStringContainsString('DB_CONNECTION" != "sqlite"', $script);
        $this->assertStringContainsString('case "$DB_DATABASE" in', $script);
        $this->assertStringContainsString('/*)', $script);
        $this->assertStringContainsString('[ ! -w "$database_directory" ]', $script);
        $this->assertStringContainsString('[ ! -w "$DB_DATABASE" ]', $script);
    }

    public function test_startup_rebuilds_and_optimizes_before_starting_frankenphp(): void
    {
        $script = $this->contents('scripts/start-demo.sh');

        $this->assertStringStartsWith("#!/bin/sh\n", $script);
        $this->assertStringContainsString('"severity":"ERROR"', $script);
        $this->assertStringContainsString('>&2', $script);
        $this->assertMatchesRegularExpression(
            '/optimize:clear.*migrate:fresh --seed --force.*optimize.*exec frankenphp run --config \/etc\/frankenphp\/Caddyfile/s',
            $script,
        );
    }

    public function test_dockerfile_builds_assets_and_vendors_then_runs_as_an_unprivileged_user(): void
    {
        $dockerfile = $this->contents('Dockerfile');

        $this->assertStringContainsString('FROM node:24', $dockerfile);
        $this->assertStringContainsString('FROM composer:2', $dockerfile);
        $this->assertStringContainsString('FROM dunglas/frankenphp:1-php8.4-bookworm', $dockerfile);

        foreach (['pdo_sqlite', 'intl', 'zip', 'opcache'] as $extension) {
            $this->assertStringContainsString($extension, $dockerfile);
        }

        $this->assertStringContainsString(
            'COPY --from=vendor /app/vendor/tightenco/ziggy /app/vendor/tightenco/ziggy',
            $dockerfile,
        );
        $this->assertStringContainsString('COPY Caddyfile /etc/frankenphp/Caddyfile', $dockerfile);
        $this->assertStringContainsString('/tmp/drm', $dockerfile);
        $this->assertStringContainsString('storage/framework/cache', $dockerfile);
        $this->assertStringContainsString('storage/framework/sessions', $dockerfile);
        $this->assertStringContainsString('storage/framework/views', $dockerfile);
        $this->assertStringContainsString('storage/logs', $dockerfile);
        $this->assertStringContainsString('bootstrap/cache', $dockerfile);
        $this->assertStringContainsString('setcap -r /usr/local/bin/frankenphp', $dockerfile);
        $this->assertStringContainsString('CACHE_STORE=file', $dockerfile);
        $this->assertStringContainsString('XDG_CONFIG_HOME=/tmp/drm/config', $dockerfile);
        $this->assertStringContainsString('XDG_DATA_HOME=/tmp/drm/data', $dockerfile);
        $this->assertStringContainsString('HOME=/tmp/drm', $dockerfile);
        $this->assertStringContainsString('--no-create-home', $dockerfile);
        $this->assertStringNotContainsString('--create-home', $dockerfile);
        $this->assertStringContainsString('USER app', $dockerfile);

        $chownStart = strpos($dockerfile, 'chown -R app:app');
        $chownEnd = strpos($dockerfile, '&& chmod', $chownStart);
        $chownPaths = substr($dockerfile, $chownStart, $chownEnd - $chownStart);

        foreach ([
            '/tmp/drm',
            '/app/storage/framework/cache',
            '/app/storage/framework/sessions',
            '/app/storage/framework/views',
            '/app/storage/logs',
            '/app/bootstrap/cache',
        ] as $writablePath) {
            $this->assertStringContainsString($writablePath, $chownPaths);
        }

        $this->assertStringNotContainsString("/app/storage/framework \\\n", $chownPaths);
        $this->assertLessThan(
            strpos($dockerfile, 'ENTRYPOINT'),
            strpos($dockerfile, 'COPY Caddyfile /etc/frankenphp/Caddyfile'),
        );
    }

    public function test_caddyfile_exposes_only_the_plain_http_application_contract(): void
    {
        $caddyfile = $this->contents('Caddyfile');

        $this->assertStringContainsString('auto_https off', $caddyfile);
        $this->assertStringContainsString(
            'http://0.0.0.0:{$PORT:8080}, http://:{$PORT:8080}',
            $caddyfile,
        );
        $this->assertStringContainsString('root * /app/public', $caddyfile);
        $this->assertStringContainsString('encode ', $caddyfile);
        $this->assertStringContainsString('php_server', $caddyfile);
    }

    public function test_docker_context_excludes_local_and_secret_bearing_files(): void
    {
        $dockerignore = $this->contents('.dockerignore');

        foreach ([
            '.git',
            '.env*',
            '!.env.example',
            'tests',
            'database/*.sqlite',
            'node_modules',
            'vendor',
            'storage/logs',
            'gha-creds-*.json',
        ] as $pattern) {
            $this->assertStringContainsString($pattern, $dockerignore);
        }
    }

    private function contents(string $relativePath): string
    {
        $path = dirname(__DIR__, 3).'/'.$relativePath;

        $this->assertFileExists($path);

        return file_get_contents($path);
    }
}
