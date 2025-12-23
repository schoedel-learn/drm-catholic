<?php

namespace Tests\Unit\Mcp;

use App\Mcp\Servers\PublicServer;
use App\Mcp\Tools\CreateParishFromGooglePlaceTool;
use App\Mcp\Tools\GetGooglePlaceTool;
use App\Mcp\Tools\SearchGooglePlacesTool;
use App\Models\Jurisdiction;
use App\Models\Parish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GooglePlacesToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_google_places_tool_returns_candidates(): void
    {
        config(['services.google_places.api_key' => 'test-key']);

        Http::fake([
            'https://maps.googleapis.com/maps/api/place/textsearch/json*' => Http::response([
                'status' => 'OK',
                'results' => [
                    [
                        'place_id' => 'place_1',
                        'name' => 'St Mary Parish',
                        'formatted_address' => '123 Church St, Madison, WI',
                    ],
                ],
            ], 200),
        ]);

        $response = PublicServer::tool(SearchGooglePlacesTool::class, [
            'query' => 'St Mary Madison WI',
            'limit' => 5,
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('search-google-places')
            ->assertSee('place_1')
            ->assertSee('St Mary Parish');
    }

    public function test_search_google_places_tool_sends_location_bias_params_when_provided(): void
    {
        config(['services.google_places.api_key' => 'test-key']);

        Http::fake([
            'https://maps.googleapis.com/maps/api/place/textsearch/json*' => Http::response([
                'status' => 'OK',
                'results' => [],
            ], 200),
        ]);

        $response = PublicServer::tool(SearchGooglePlacesTool::class, [
            'query' => 'St Mary',
            'limit' => 5,
            'lat' => 43,
            'lng' => -89,
            'radius_meters' => 10000,
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('search-google-places');

        Http::assertSent(function ($request) {
            if (! str_starts_with($request->url(), 'https://maps.googleapis.com/maps/api/place/textsearch/json')) {
                return false;
            }

            $data = $request->data();

            return ($data['location'] ?? null) === '43,-89'
                && (int) ($data['radius'] ?? 0) === 10000;
        });
    }

    public function test_get_google_place_tool_returns_details(): void
    {
        config(['services.google_places.api_key' => 'test-key']);

        Http::fake([
            'https://maps.googleapis.com/maps/api/place/details/json*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'place_id' => 'place_1',
                    'name' => 'St Mary Parish',
                    'formatted_address' => '123 Church St, Madison, WI',
                    'url' => 'https://maps.google.com/?q=place_1',
                    'geometry' => [
                        'location' => [
                            'lat' => 43.0,
                            'lng' => -89.0,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = PublicServer::tool(GetGooglePlaceTool::class, [
            'place_id' => 'place_1',
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('get-google-place')
            ->assertSee('123 Church St')
            ->assertSee('https://maps.google.com/?q=place_1');
    }

    public function test_create_parish_from_google_place_tool_creates_parish_with_google_fields(): void
    {
        config(['services.google_places.api_key' => 'test-key']);

        $diocese = Jurisdiction::create([
            'name' => 'Diocese of Places',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'Exampleville',
            'address' => ['line1' => '1 Main St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        Http::fake([
            'https://maps.googleapis.com/maps/api/place/details/json*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'place_id' => 'place_1',
                    'name' => 'St Mary Parish',
                    'formatted_address' => '123 Church St, Madison, WI',
                    'url' => 'https://maps.google.com/?q=place_1',
                    'geometry' => [
                        'location' => [
                            'lat' => 43.0,
                            'lng' => -89.0,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = PublicServer::tool(CreateParishFromGooglePlaceTool::class, [
            'diocese_id' => $diocese->id,
            'place_id' => 'place_1',
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('create-parish-from-google-place')
            ->assertSee('St Mary Parish')
            ->assertSee('place_1');

        $this->assertDatabaseHas('parishes', [
            'diocese_id' => $diocese->id,
            'name' => 'St Mary Parish',
            'google_place_id' => 'place_1',
            'google_formatted_address' => '123 Church St, Madison, WI',
        ]);
    }

    public function test_create_parish_from_google_place_tool_is_idempotent_when_place_id_already_exists(): void
    {
        config(['services.google_places.api_key' => 'test-key']);

        $diocese = Jurisdiction::create([
            'name' => 'Diocese of Idempotence',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'Exampleville',
            'address' => ['line1' => '1 Main St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $existing = Parish::create([
            'diocese_id' => $diocese->id,
            'name' => 'Existing Parish',
            'address' => ['formatted' => 'Existing Address', 'source' => 'google_places'],
            'google_place_id' => 'place_existing',
            'google_formatted_address' => 'Existing Address',
            'google_maps_url' => 'https://maps.google.com/?q=place_existing',
            'google_lat' => 43.0,
            'google_lng' => -89.0,
        ]);

        Http::fake();

        $response = PublicServer::tool(CreateParishFromGooglePlaceTool::class, [
            'diocese_id' => $diocese->id,
            'place_id' => 'place_existing',
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('create-parish-from-google-place')
            ->assertSee((string) $existing->id)
            ->assertSee('place_existing');

        Http::assertNothingSent();

        $this->assertDatabaseHas('parishes', [
            'id' => $existing->id,
            'google_place_id' => 'place_existing',
        ]);
    }

    public function test_create_parish_from_google_place_tool_errors_if_place_id_is_linked_to_different_diocese(): void
    {
        config(['services.google_places.api_key' => 'test-key']);

        $dioceseA = Jurisdiction::create([
            'name' => 'Diocese A',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'City A',
            'address' => ['line1' => '1 Main St', 'city' => 'City A', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $dioceseB = Jurisdiction::create([
            'name' => 'Diocese B',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'City B',
            'address' => ['line1' => '2 Main St', 'city' => 'City B', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $existing = Parish::create([
            'diocese_id' => $dioceseA->id,
            'name' => 'Existing Parish',
            'address' => ['formatted' => 'Existing Address', 'source' => 'google_places'],
            'google_place_id' => 'place_shared',
            'google_formatted_address' => 'Existing Address',
            'google_maps_url' => 'https://maps.google.com/?q=place_shared',
            'google_lat' => 43.0,
            'google_lng' => -89.0,
        ]);

        Http::fake();

        $response = PublicServer::tool(CreateParishFromGooglePlaceTool::class, [
            'diocese_id' => $dioceseB->id,
            'place_id' => 'place_shared',
        ]);

        $response
            ->assertHasErrors()
            ->assertName('create-parish-from-google-place')
            ->assertSee((string) $existing->id)
            ->assertSee((string) $dioceseA->id)
            ->assertSee((string) $dioceseB->id);

        Http::assertNothingSent();
    }
}
