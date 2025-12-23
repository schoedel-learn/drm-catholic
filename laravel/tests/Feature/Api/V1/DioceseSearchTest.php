<?php

namespace Tests\Feature\Api\V1;

use App\Models\Jurisdiction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DioceseSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_matching_non_external_dioceses_by_default(): void
    {
        Jurisdiction::factory()->create([
            'name' => 'Diocese of Springfield',
            'is_external' => false,
        ]);

        Jurisdiction::factory()->create([
            'name' => 'Archdiocese of Shelbyville',
            'is_external' => false,
        ]);

        Jurisdiction::factory()->create([
            'name' => 'Diocese of External Place',
            'is_external' => true,
        ]);

        $response = $this->getJson('/api/v1/dioceses/search?query=spring');

        $response->assertOk();
        $response->assertJson([
            'query' => 'spring',
        ]);

        $response->assertJsonCount(1, 'results');
        $response->assertJsonPath('results.0.name', 'Diocese of Springfield');
    }

    public function test_search_can_include_external_dioceses_when_requested(): void
    {
        Jurisdiction::factory()->create([
            'name' => 'Diocese of External Place',
            'is_external' => true,
        ]);

        $response = $this->getJson('/api/v1/dioceses/search?query=external&include_external=1');

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonPath('results.0.name', 'Diocese of External Place');
    }
}
