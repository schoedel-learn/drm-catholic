<?php

namespace Tests\Feature\Demo;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Exercises scripts/verify-demo.zsh against a real, local HTTP fixture so the
 * demo-contract assertions are proven by behaviour, not by matching strings in
 * the script source. Every case is deterministic and fully offline.
 */
class VerifyDemoScriptTest extends TestCase
{
    private const EXPECTED_SHA = '1234567890abcdef1234567890abcdef12345678';

    /**
     * @var list<Process>
     */
    private array $servers = [];

    protected function tearDown(): void
    {
        foreach ($this->servers as $server) {
            if ($server->isRunning()) {
                $server->stop(1);
            }
        }

        $this->servers = [];

        parent::tearDown();
    }

    public function test_it_passes_when_every_demo_contract_check_holds(): void
    {
        $process = $this->runVerify('ok', self::EXPECTED_SHA, self::EXPECTED_SHA);

        $this->assertSame(
            0,
            $process->getExitCode(),
            $this->diagnostics('expected verify-demo to succeed', $process),
        );
        $this->assertStringContainsString('All demo checks passed', $process->getOutput());
    }

    public function test_it_fails_when_the_reported_sha_does_not_match(): void
    {
        $process = $this->runVerify('bad-sha', self::EXPECTED_SHA, self::EXPECTED_SHA);

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertMatchesRegularExpression('/sha/i', $process->getErrorOutput());
    }

    public function test_it_fails_when_the_noindex_header_is_missing(): void
    {
        $process = $this->runVerify('no-noindex', self::EXPECTED_SHA, self::EXPECTED_SHA);

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertMatchesRegularExpression('/X-Robots-Tag|noindex/i', $process->getErrorOutput());
    }

    public function test_it_fails_when_registration_is_not_disabled(): void
    {
        $process = $this->runVerify('register-enabled', self::EXPECTED_SHA, self::EXPECTED_SHA);

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertMatchesRegularExpression('/register/i', $process->getErrorOutput());
    }

    public function test_it_fails_when_password_reset_is_not_disabled(): void
    {
        $process = $this->runVerify('forgot-enabled', self::EXPECTED_SHA, self::EXPECTED_SHA);

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertMatchesRegularExpression('/forgot-password/i', $process->getErrorOutput());
    }

    public function test_it_fails_when_health_reports_unavailable(): void
    {
        $process = $this->runVerify('health-503', self::EXPECTED_SHA, self::EXPECTED_SHA);

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertMatchesRegularExpression('/health/i', $process->getErrorOutput());
    }

    public function test_it_fails_loudly_when_the_service_never_becomes_ready(): void
    {
        $process = $this->runVerify('down', self::EXPECTED_SHA, self::EXPECTED_SHA);

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertMatchesRegularExpression('/up|ready/i', $process->getErrorOutput());
    }

    public function test_it_rejects_invalid_invocation(): void
    {
        $script = $this->scriptPath();
        $process = new Process(['zsh', $script, '--base-url', 'http://127.0.0.1:1']);
        $process->run();

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertMatchesRegularExpression('/usage|expected-sha/i', $process->getErrorOutput());
    }

    private function runVerify(string $scenario, string $fixtureSha, string $expectedSha): Process
    {
        $port = $this->reserveFreePort();
        $this->startFixtureServer($port, $scenario, $fixtureSha);

        $script = $this->scriptPath();
        $process = new Process([
            'zsh',
            $script,
            '--base-url',
            "http://127.0.0.1:{$port}",
            '--expected-sha',
            $expectedSha,
            '--ready-timeout',
            '4',
            '--timeout',
            '4',
        ]);
        $process->run();

        return $process;
    }

    private function startFixtureServer(int $port, string $scenario, string $sha): void
    {
        $root = dirname(__DIR__, 3);
        $router = $root.'/tests/Fixtures/demo-verify-server.php';
        $this->assertFileExists($router);

        $server = new Process(
            ['php', '-S', "127.0.0.1:{$port}", $router],
            $root,
            ['FIXTURE_SCENARIO' => $scenario, 'FIXTURE_GIT_SHA' => $sha],
        );
        $server->start();
        $this->servers[] = $server;

        $deadline = microtime(true) + 5.0;
        while (microtime(true) < $deadline) {
            set_error_handler(static fn () => true);
            $connection = fsockopen('127.0.0.1', $port, $errno, $errstr, 0.2);
            restore_error_handler();

            if ($connection !== false) {
                fclose($connection);

                return;
            }

            usleep(50_000);
        }

        $this->fail("fixture server did not start on port {$port}: ".$server->getErrorOutput());
    }

    private function reserveFreePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $this->assertNotFalse($socket, "unable to reserve a free port: {$errstr}");

        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        $port = (int) substr((string) $name, (int) strrpos((string) $name, ':') + 1);
        $this->assertGreaterThan(0, $port);

        return $port;
    }

    private function scriptPath(): string
    {
        $script = dirname(__DIR__, 3).'/scripts/verify-demo.zsh';
        $this->assertFileExists($script);

        return $script;
    }

    private function diagnostics(string $message, Process $process): string
    {
        return sprintf(
            "%s\nEXIT: %s\nSTDOUT:\n%s\nSTDERR:\n%s",
            $message,
            (string) $process->getExitCode(),
            $process->getOutput(),
            $process->getErrorOutput(),
        );
    }
}
