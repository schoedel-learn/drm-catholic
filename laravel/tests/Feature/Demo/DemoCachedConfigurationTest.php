<?php

namespace Tests\Feature\Demo;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;

class DemoCachedConfigurationTest extends TestCase
{
    private const FORTIFY_PUBLIC_ROUTES = [
        ['GET|HEAD', 'register', 'register'],
        ['POST', 'register', 'register.store'],
        ['GET|HEAD', 'forgot-password', 'password.request'],
        ['POST', 'forgot-password', 'password.email'],
        ['GET|HEAD', 'reset-password/{token}', 'password.reset'],
        ['POST', 'reset-password', 'password.update'],
    ];

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_optimized_demo_configuration_removes_public_account_routes_and_forces_safe_drivers(): void
    {
        $this->withOptimizedConfiguration(true, function (array $routes, array $configuration): void {
            foreach (self::FORTIFY_PUBLIC_ROUTES as [$method, $uri, $name]) {
                $this->assertRouteMissing($routes, $method, $uri, $name);
            }

            $this->assertSame('log', $configuration['mail']);
            $this->assertSame('sync', $configuration['queue']);
            $this->assertTrue($configuration['demo']);
        });
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_optimized_local_configuration_retains_public_account_routes_and_configured_drivers(): void
    {
        $this->withOptimizedConfiguration(false, function (array $routes, array $configuration): void {
            foreach (self::FORTIFY_PUBLIC_ROUTES as [$method, $uri, $name]) {
                $this->assertRoutePresent($routes, $method, $uri, $name);
            }

            $this->assertSame('smtp', $configuration['mail']);
            $this->assertSame('database', $configuration['queue']);
            $this->assertFalse($configuration['demo']);
        });
    }

    private function withOptimizedConfiguration(bool $demoMode, callable $assertions): void
    {
        $root = dirname(__DIR__, 3);
        $cacheDirectory = $root.'/storage/framework/testing/demo-cache/'.bin2hex(random_bytes(8));
        $environment = $this->environment($cacheDirectory, $demoMode);

        mkdir($cacheDirectory.'/views', 0777, true);

        try {
            $this->runArtisan($root, $environment, ['optimize:clear']);
            $this->runArtisan($root, $environment, ['optimize']);

            $inspectionEnvironment = [
                ...$environment,
                'DEMO_MODE' => $demoMode ? 'false' : 'true',
                'MAIL_MAILER' => 'array',
                'QUEUE_CONNECTION' => 'sync',
            ];

            $routes = json_decode(
                $this->runArtisan($root, $inspectionEnvironment, ['route:list', '--json']),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
            $configuration = json_decode(
                $this->runPhp($root, $inspectionEnvironment, <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode([
    'demo' => config('demo.enabled'),
    'mail' => config('mail.default'),
    'queue' => config('queue.default'),
], JSON_THROW_ON_ERROR);
PHP),
                true,
                flags: JSON_THROW_ON_ERROR,
            );

            $assertions($routes, $configuration);
        } finally {
            $this->runCleanup($root, $environment);
            $this->deleteDirectory($cacheDirectory);
        }
    }

    private function environment(string $cacheDirectory, bool $demoMode): array
    {
        return [
            'APP_CONFIG_CACHE' => $cacheDirectory.'/config.php',
            'APP_ENV' => 'testing',
            'APP_EVENTS_CACHE' => $cacheDirectory.'/events.php',
            'APP_PACKAGES_CACHE' => $cacheDirectory.'/packages.php',
            'APP_ROUTES_CACHE' => $cacheDirectory.'/routes.php',
            'APP_SERVICES_CACHE' => $cacheDirectory.'/services.php',
            'CACHE_STORE' => 'array',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DEMO_MODE' => $demoMode ? 'true' : 'false',
            'MAIL_MAILER' => 'smtp',
            'QUEUE_CONNECTION' => 'database',
            'SESSION_DRIVER' => 'array',
            'VIEW_COMPILED_PATH' => $cacheDirectory.'/views',
        ];
    }

    private function runArtisan(string $root, array $environment, array $arguments): string
    {
        return $this->runProcess(
            ['php', 'artisan', ...$arguments, '--no-ansi'],
            $root,
            $environment,
        );
    }

    private function runPhp(string $root, array $environment, string $code): string
    {
        return $this->runProcess(['php', '-r', $code], $root, $environment);
    }

    private function runProcess(array $command, string $root, array $environment): string
    {
        $process = new Process($command, $root, $environment);
        $process->setTimeout(120);
        $process->run();

        $this->assertTrue(
            $process->isSuccessful(),
            sprintf(
                "Command failed: %s\nSTDOUT:\n%s\nSTDERR:\n%s",
                $process->getCommandLine(),
                $process->getOutput(),
                $process->getErrorOutput(),
            ),
        );

        return $process->getOutput();
    }

    private function runCleanup(string $root, array $environment): void
    {
        $process = new Process(
            ['php', 'artisan', 'optimize:clear', '--no-ansi'],
            $root,
            $environment,
        );
        $process->setTimeout(120);
        $process->run();
    }

    private function assertRouteMissing(array $routes, string $method, string $uri, string $name): void
    {
        $this->assertFalse($this->hasRoute($routes, $method, $uri, $name));
    }

    private function assertRoutePresent(array $routes, string $method, string $uri, string $name): void
    {
        $this->assertTrue($this->hasRoute($routes, $method, $uri, $name));
    }

    private function hasRoute(array $routes, string $method, string $uri, string $name): bool
    {
        foreach ($routes as $route) {
            if ($route['method'] === $method && $route['uri'] === $uri && $route['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($path);
    }
}
