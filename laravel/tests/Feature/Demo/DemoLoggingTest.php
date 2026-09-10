<?php

namespace Tests\Feature\Demo;

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
Illuminate\Support\Facades\Log::warning('Demo structured log.', ['safe' => 'value']);
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
        $this->assertSame(['safe' => 'value'], $record['context']);
        $this->assertArrayHasKey('timestamp', $record);
        $this->assertArrayNotHasKey('environment', $record);
        $this->assertArrayNotHasKey('user_password', $record);
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
}
