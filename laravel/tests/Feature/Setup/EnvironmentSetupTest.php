<?php

namespace Tests\Feature\Setup;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Process\Process;

class EnvironmentSetupTest extends TestCase
{
    /**
     * @var list<string>
     */
    private array $fixtures = [];

    protected function tearDown(): void
    {
        foreach ($this->fixtures as $fixture) {
            $this->deleteDirectory($fixture);
        }

        parent::tearDown();
    }

    public function test_prepare_only_preserves_an_existing_env_file(): void
    {
        $fixture = $this->makeFixture('existing-env');
        $sentinelEnv = "APP_NAME=\"Sentinel\"\nAPP_KEY=base64:keep-this-key\nCUSTOM_VALUE=keep me exactly\n";

        file_put_contents($fixture.'/.env.example', "APP_NAME=\"Fixture\"\nAPP_KEY=\n");
        file_put_contents($fixture.'/.env', $sentinelEnv);

        $this->runPrepareOnly($fixture);

        $this->assertSame($sentinelEnv, file_get_contents($fixture.'/.env'));
    }

    public function test_prepare_only_bootstraps_env_and_sqlite_when_missing(): void
    {
        $fixture = $this->makeFixture('missing-env');
        $exampleEnv = "APP_NAME=\"Fixture\"\nAPP_KEY=\nAPP_URL=http://127.0.0.1:8000\n";

        file_put_contents($fixture.'/.env.example', $exampleEnv);

        $this->runPrepareOnly($fixture);

        $this->assertFileExists($fixture.'/.env');
        $this->assertSame($exampleEnv, file_get_contents($fixture.'/.env'));
        $this->assertFileExists($fixture.'/database/database.sqlite');
    }

    private function makeFixture(string $name): string
    {
        $fixture = dirname(__DIR__, 3).'/storage/framework/testing/setup-local/'.$name.'-'.bin2hex(random_bytes(4));
        $scriptSource = dirname(__DIR__, 3).'/scripts/setup-local.zsh';

        $this->fixtures[] = $fixture;

        mkdir($fixture.'/scripts', 0777, true);

        $this->assertFileExists($scriptSource);
        $this->assertTrue(copy($scriptSource, $fixture.'/scripts/setup-local.zsh'));
        chmod($fixture.'/scripts/setup-local.zsh', 0755);

        return $fixture;
    }

    private function runPrepareOnly(string $fixture): void
    {
        $process = new Process(['zsh', 'scripts/setup-local.zsh', '--prepare-only'], $fixture);
        $process->run();

        $this->assertTrue(
            $process->isSuccessful(),
            sprintf(
                "prepare-only failed.\nSTDOUT:\n%s\nSTDERR:\n%s",
                $process->getOutput(),
                $process->getErrorOutput(),
            ),
        );
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
