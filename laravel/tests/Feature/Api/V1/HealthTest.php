<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_reports_database_and_deployment_readiness(): void
    {
        config()->set('demo.git_sha', 'test-deployment-sha');

        DB::shouldReceive('selectOne')
            ->once()
            ->with('select 1')
            ->andReturn((object) ['health' => 1]);

        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'database' => 'ok',
                'git_sha' => 'test-deployment-sha',
            ]);

        $this->assertSame(
            route('api.v1.health', absolute: false),
            '/api/v1/health',
        );
    }

    public function test_health_reports_database_failure_without_exposing_exception_details(): void
    {
        config()->set('demo.git_sha', 'test-deployment-sha');

        DB::shouldReceive('selectOne')
            ->once()
            ->with('select 1')
            ->andThrow(new RuntimeException('health-sensitive-sentinel'));

        Log::shouldReceive('error')
            ->once()
            ->with(
                'Health database probe failed.',
                \Mockery::on(function (array $context): bool {
                    $encoded = json_encode($context, JSON_THROW_ON_ERROR);

                    return $context === [
                        'exception_type' => RuntimeException::class,
                        'git_sha' => 'test-deployment-sha',
                    ] && ! str_contains($encoded, 'health-sensitive-sentinel');
                }),
            );

        $response = $this->getJson('/api/v1/health')
            ->assertServiceUnavailable()
            ->assertExactJson([
                'status' => 'unavailable',
                'database' => 'error',
                'git_sha' => 'test-deployment-sha',
            ]);

        $this->assertStringNotContainsString(
            'health-sensitive-sentinel',
            $response->getContent(),
        );
    }

    public function test_liveness_endpoint_remains_available(): void
    {
        $this->get('/up')->assertOk();
    }
}
