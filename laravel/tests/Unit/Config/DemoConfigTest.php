<?php

namespace Tests\Unit\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class DemoConfigTest extends TestCase
{
    #[DataProvider('demoModeEnvironmentValues')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_demo_mode_is_enabled_only_by_laravel_boolean_true(
        ?string $environmentValue,
        bool $expected,
    ): void {
        $this->setEnvironmentValue('DEMO_MODE', $environmentValue);

        $config = $this->loadDemoConfig();

        $this->assertSame($expected, $config['enabled']);
    }

    public static function demoModeEnvironmentValues(): array
    {
        return [
            'true' => ['true', true],
            'false' => ['false', false],
            'missing' => [null, false],
            'invalid text' => ['not-true', false],
        ];
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_deployment_sha_uses_the_approved_environment_key(): void
    {
        $this->setEnvironmentValue('GIT_SHA', 'legacy-sha');
        $this->setEnvironmentValue('DEPLOYMENT_GIT_SHA', 'deployment-sha');

        $config = $this->loadDemoConfig();

        $this->assertSame('deployment-sha', $config['git_sha']);
    }

    private function loadDemoConfig(): array
    {
        return require dirname(__DIR__, 3).'/config/demo.php';
    }

    private function setEnvironmentValue(string $key, ?string $value): void
    {
        if ($value === null) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);

            return;
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}
