<?php

namespace Tests\Unit\Mcp;

use App\Mcp\Servers\PublicServer;
use App\Mcp\Tools\SearchParishesTool;
use App\Models\Jurisdiction;
use App\Models\Parish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchParishesToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_matching_parishes_as_structured_json_text(): void
    {
        $diocese = Jurisdiction::create([
            'name' => 'Diocese of Example',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'Exampleville',
            'address' => ['line1' => '1 Main St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        Parish::create([
            'diocese_id' => $diocese->id,
            'name' => 'St Mary Parish',
            'address' => ['line1' => '10 Church St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
            'phone' => '555-0001',
            'email' => 'stmary@example.test',
        ]);

        Parish::create([
            'diocese_id' => $diocese->id,
            'name' => 'St Joseph Parish',
            'address' => ['line1' => '20 Church St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $response = PublicServer::tool(SearchParishesTool::class, [
            'query' => 'St ',
            'limit' => 10,
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('search-parishes')
            ->assertSee('"count":2')
            ->assertSee('St Mary Parish')
            ->assertSee('St Joseph Parish');
    }
}
