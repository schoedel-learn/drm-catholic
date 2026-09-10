<?php

namespace Tests\Feature\Demo;

use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DemoLoggingTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_demo_logging_writes_structured_json_to_stderr(): void
    {
        $root = dirname(__DIR__, 3);
        $environment = [
            'APP_ENV' => 'testing',
            'DEMO_MODE' => 'true',
            'DEPLOYMENT_GIT_SHA' => 'test-deployment-sha',
            'LOG_LEVEL' => 'debug',
        ];
        $code = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Log::warning('Demo structured log.', [
    'safe' => 'value',
    'password' => 'password-sentinel',
    'nested' => ['api_token' => 'token-sentinel'],
    'object_payload' => (object) [
        'clientSecret' => 'object-secret-sentinel',
        'safe' => 'object-safe-value',
    ],
    'exception' => new RuntimeException('exception-sentinel'),
]);
echo json_encode([
    'channel' => config('logging.default'),
    'stream' => config('logging.channels.demo_stderr.handler_with.stream'),
], JSON_THROW_ON_ERROR);
PHP;

        $process = new Process(['php', '-r', $code], $root, $environment);
        $process->mustRun();

        $configuration = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        $records = array_values(array_filter(explode("\n", trim($process->getErrorOutput()))));

        $this->assertSame('demo_stderr', $configuration['channel']);
        $this->assertSame('php://stderr', $configuration['stream']);
        $this->assertCount(1, $records);

        $record = json_decode($records[0], true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('WARNING', $record['severity']);
        $this->assertSame('Demo structured log.', $record['message']);
        $this->assertSame('test-deployment-sha', $record['git_sha']);
        $this->assertSame('value', $record['context']['safe']);
        $this->assertSame('[REDACTED]', $record['context']['password']);
        $this->assertSame('[REDACTED]', $record['context']['nested']['api_token']);
        $this->assertSame(
            [
                'clientSecret' => '[REDACTED]',
                'safe' => 'object-safe-value',
            ],
            $record['context']['object_payload'],
        );
        $this->assertSame(
            ['exception_type' => \RuntimeException::class],
            $record['context']['exception'],
        );
        $this->assertArrayHasKey('timestamp', $record);
        $this->assertArrayNotHasKey('environment', $record);
        $this->assertArrayNotHasKey('user_password', $record);
        $this->assertStringNotContainsString('password-sentinel', $records[0]);
        $this->assertStringNotContainsString('token-sentinel', $records[0]);
        $this->assertStringNotContainsString('object-secret-sentinel', $records[0]);
        $this->assertStringNotContainsString('exception-sentinel', $records[0]);
    }

    public function test_local_mode_retains_the_configured_log_channel(): void
    {
        $root = dirname(__DIR__, 3);
        $code = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo config('logging.default');
PHP;
        $process = new Process(['php', '-r', $code], $root, [
            'APP_ENV' => 'testing',
            'DEMO_MODE' => 'false',
            'LOG_CHANNEL' => 'stack',
        ]);
        $process->mustRun();

        $this->assertSame('stack', $process->getOutput());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_failed_health_probe_writes_only_safe_details_to_stderr(): void
    {
        $root = dirname(__DIR__, 3);
        $environment = [
            'APP_ENV' => 'testing',
            'DEMO_MODE' => 'true',
            'DEPLOYMENT_GIT_SHA' => 'test-deployment-sha',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => '/missing/health-sensitive-sentinel/database.sqlite',
            'LOG_LEVEL' => 'debug',
        ];
        $code = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$response = $app->make(App\Http\Controllers\Api\V1\HealthController::class)();
echo $response->getContent();
PHP;

        $process = new Process(['php', '-r', $code], $root, $environment);
        $process->mustRun();

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'status' => 'unavailable',
                'database' => 'error',
                'git_sha' => 'test-deployment-sha',
            ], JSON_THROW_ON_ERROR),
            $process->getOutput(),
        );

        $record = json_decode(
            trim($process->getErrorOutput()),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame('ERROR', $record['severity']);
        $this->assertSame('Health database probe failed.', $record['message']);
        $this->assertSame('test-deployment-sha', $record['git_sha']);
        $this->assertSame(
            [
                'exception_type' => QueryException::class,
                'git_sha' => 'test-deployment-sha',
            ],
            $record['context'],
        );
        $this->assertStringNotContainsString(
            'health-sensitive-sentinel',
            $process->getErrorOutput(),
        );
    }
}
