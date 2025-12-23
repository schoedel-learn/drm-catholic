<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GooglePlacesTest extends TestCase
{
    public function test_google_places_search_endpoint_returns_candidates(): void
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
                    [
                        'place_id' => 'place_2',
                        'name' => 'St Joseph Parish',
                        'formatted_address' => '456 Main St, Madison, WI',
                    ],
                ],
            ], 200),
        ]);

        $this->getJson('/api/v1/google-places/search?query=St%20Mary&limit=2')
            ->assertOk()
            ->assertJson([
                'query' => 'St Mary',
                'count' => 2,
            ])
            ->assertJsonPath('results.0.place_id', 'place_1');
    }

    public function test_google_places_search_endpoint_sends_location_bias_params_when_provided(): void
    {
        config(['services.google_places.api_key' => 'test-key']);

        Http::fake([
            'https://maps.googleapis.com/maps/api/place/textsearch/json*' => Http::response([
                'status' => 'OK',
                'results' => [],
            ], 200),
        ]);

        $this->getJson('/api/v1/google-places/search?query=St%20Mary&limit=2&lat=43&lng=-89&radius_meters=10000')
            ->assertOk();

        Http::assertSent(function ($request) {
            if (! str_starts_with($request->url(), 'https://maps.googleapis.com/maps/api/place/textsearch/json')) {
                return false;
            }

            $data = $request->data();

            return ($data['location'] ?? null) === '43,-89'
                && (int) ($data['radius'] ?? 0) === 10000;
        });
    }

    public function test_google_places_details_endpoint_returns_place_details(): void
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

        $this->getJson('/api/v1/google-places/details?place_id=place_1')
            ->assertOk()
            ->assertJson([
                'place_id' => 'place_1',
                'name' => 'St Mary Parish',
                'formatted_address' => '123 Church St, Madison, WI',
                'maps_url' => 'https://maps.google.com/?q=place_1',
                'lat' => 43.0,
                'lng' => -89.0,
            ]);
    }
}
