<?php

namespace Tests\Feature\Demo;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class StartupConfigurationTest extends TestCase
{
    /** @var list<string> */
    private array $sandboxes = [];

    protected function tearDown(): void
    {
        foreach ($this->sandboxes as $sandbox) {
            $this->removeDirectory($sandbox);
        }
        $this->sandboxes = [];

        parent::tearDown();
    }

    public function test_invalid_configuration_fails_before_any_startup_command_is_invoked(): void
    {
        $sandbox = $this->makeStartupSandbox();

        $environment = $this->validStartupEnvironment();
        $environment['APP_KEY'] = '';

        $result = $this->runStartupScript($sandbox, $environment);

        $this->assertNotSame(0, $result['exitCode']);
        $this->assertSame([], $this->readStubInvocations($sandbox));

        $event = json_decode(trim($result['stderr']), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('demo_startup_failed', $event['event']);
        $this->assertSame('validation', $event['stage']);
        $this->assertSame('ERROR', $event['severity']);
    }

    public function test_valid_configuration_runs_artisan_commands_in_order_then_hands_off_to_frankenphp(): void
    {
        $sandbox = $this->makeStartupSandbox();

        $result = $this->runStartupScript($sandbox, $this->validStartupEnvironment());

        $this->assertSame(0, $result['exitCode']);
        $this->assertSame([
            'php artisan optimize:clear',
            'php artisan migrate:fresh --seed --force',
            'php artisan optimize',
            'frankenphp run --config /etc/frankenphp/Caddyfile',
        ], $this->readStubInvocations($sandbox));
    }

    public function test_a_failing_startup_command_halts_before_frankenphp_and_reports_its_stage(): void
    {
        $sandbox = $this->makeStartupSandbox();
        $this->makeStubFailOn($sandbox, 'php', 'migrate:fresh');

        $result = $this->runStartupScript($sandbox, $this->validStartupEnvironment());

        $this->assertNotSame(0, $result['exitCode']);
        $this->assertSame([
            'php artisan optimize:clear',
            'php artisan migrate:fresh --seed --force',
        ], $this->readStubInvocations($sandbox));

        $event = json_decode(trim($result['stderr']), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('demo_startup_failed', $event['event']);
        $this->assertSame('database_rebuild', $event['stage']);
        $this->assertSame('ERROR', $event['severity']);
    }

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
            'bootstrap/cache/*',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
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

    /**
     * Build an isolated sandbox root containing a fake "/app" writable tree
     * and stubbed "php"/"frankenphp" executables, so the real start-demo.sh
     * script can be executed end-to-end without touching real migrations,
     * a real server, or the host filesystem's actual /app path.
     */
    private function makeStartupSandbox(): string
    {
        if (! $this->commandExists('bwrap')) {
            $this->markTestSkipped('bwrap (bubblewrap) is required to sandbox scripts/start-demo.sh for behavioral testing.');
        }

        $root = dirname(__DIR__, 3).'/storage/framework/testing/startup-sandbox-'.bin2hex(random_bytes(8));

        foreach ([
            '/app/storage/framework/cache',
            '/app/storage/framework/sessions',
            '/app/storage/framework/views',
            '/app/storage/logs',
            '/app/bootstrap/cache',
            '/app/tmp/drm',
            '/stubbin',
            '/log',
        ] as $path) {
            $this->assertTrue(mkdir($root.$path, 0o755, true));
        }

        $this->writeStub($root, 'php', <<<'SH'
            #!/bin/sh
            printf '%s\n' "php $*" >> "$STUB_LOG"
            if [ -n "${STUB_FAIL_ON:-}" ]; then
                case "$*" in
                    *"$STUB_FAIL_ON"*) exit 9 ;;
                esac
            fi
            exit 0
            SH);

        $this->writeStub($root, 'frankenphp', <<<'SH'
            #!/bin/sh
            printf '%s\n' "frankenphp $*" >> "$STUB_LOG"
            exit 0
            SH);

        $this->sandboxes[] = $root;

        return $root;
    }

    private function writeStub(string $sandboxRoot, string $name, string $contents): void
    {
        $path = $sandboxRoot.'/stubbin/'.$name;
        file_put_contents($path, $contents);
        chmod($path, 0o755);
    }

    private function makeStubFailOn(string $sandboxRoot, string $stub, string $argsSubstring): void
    {
        $marker = $sandboxRoot.'/log/fail-on-'.$stub;
        file_put_contents($marker, $argsSubstring);
    }

    /**
     * @return array<string, string>
     */
    private function validStartupEnvironment(): array
    {
        return [
            'DEMO_MODE' => 'true',
            'APP_KEY' => 'base64:sandbox-test-key',
            'DEMO_USER_PASSWORD' => 'sandbox-test-password',
            'DEPLOYMENT_GIT_SHA' => 'sandbox-test-sha',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => '/app/tmp/drm/database.sqlite',
        ];
    }

    /**
     * Run the real scripts/start-demo.sh under bwrap inside a fresh, private
     * mount+user namespace: a tmpfs root with the host's /usr, /bin, /lib,
     * /lib64 and /etc bind-mounted read-only, plus the sandbox's own /app,
     * /stubbin and /log bound in. This never touches the host's real /app
     * and does not require root or sudo.
     *
     * @param  array<string, string>  $environment
     * @return array{exitCode: int, stdout: string, stderr: string}
     */
    private function runStartupScript(string $sandboxRoot, array $environment): array
    {
        $scriptPath = dirname(__DIR__, 3).'/scripts/start-demo.sh';
        $this->assertFileExists($scriptPath);

        $failOnPhpFile = $sandboxRoot.'/log/fail-on-php';
        $failOnPhp = is_file($failOnPhpFile) ? (file_get_contents($failOnPhpFile) ?: '') : '';

        $command = ['bwrap', '--unshare-all', '--die-with-parent', '--tmpfs', '/'];

        foreach (['/usr', '/bin', '/lib', '/lib64', '/etc'] as $systemPath) {
            if (is_dir($systemPath)) {
                array_push($command, '--ro-bind', $systemPath, $systemPath);
            }
        }

        array_push(
            $command,
            '--bind', $sandboxRoot.'/app', '/app',
            '--ro-bind', $sandboxRoot.'/stubbin', '/stubbin',
            '--bind', $sandboxRoot.'/log', '/log',
            '--ro-bind', $scriptPath, '/entrypoint.sh',
            '--proc', '/proc',
            '--dev', '/dev',
            '--chdir', '/app',
            '--clearenv',
            '--setenv', 'PATH', '/stubbin:/usr/bin:/bin',
            '--setenv', 'STUB_LOG', '/log/invocations.log',
            '--setenv', 'STUB_FAIL_ON', $failOnPhp,
        );

        foreach ($environment as $name => $value) {
            array_push($command, '--setenv', $name, $value);
        }

        array_push($command, '--', '/bin/sh', '/entrypoint.sh');

        $process = new Process($command);
        $process->setTimeout(30);
        $process->run();

        return [
            'exitCode' => $process->getExitCode(),
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
        ];
    }

    /**
     * @return list<string>
     */
    private function readStubInvocations(string $sandboxRoot): array
    {
        $logPath = $sandboxRoot.'/log/invocations.log';

        if (! is_file($logPath)) {
            return [];
        }

        return array_values(array_filter(explode("\n", trim(file_get_contents($logPath)))));
    }

    private function commandExists(string $binary): bool
    {
        $process = Process::fromShellCommandline('command -v '.escapeshellarg($binary));
        $process->run();

        return $process->isSuccessful();
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($directory);
    }
}
