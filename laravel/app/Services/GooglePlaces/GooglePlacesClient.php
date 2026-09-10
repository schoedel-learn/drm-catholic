<?php

namespace App\Services\GooglePlaces;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GooglePlacesClient
{
    /**
     * @return array{status: string|null, results: array<int, mixed>, error_message: string|null}
     */
    protected function performTextSearchRequest(
        string $query,
        string $apiKey,
        ?float $lat = null,
        ?float $lng = null,
        ?int $radiusMeters = null,
    ): array {
        $params = [
            'query' => $query,
            'key' => $apiKey,
        ];

        if (is_float($lat) && is_float($lng)) {
            $params['location'] = $lat.','.$lng;
            $params['radius'] = max(1, min(50000, (int) ($radiusMeters ?? 50000)));
        }

        try {
            $response = Http::timeout(15)
                ->get('https://maps.googleapis.com/maps/api/place/textsearch/json', $params)
                ->throw();
        } catch (ConnectionException $e) {
            throw ValidationException::withMessages([
                'google_places' => 'Unable to reach Google Places API.',
            ]);
        } catch (RequestException $e) {
            throw ValidationException::withMessages([
                'google_places' => 'Google Places API request failed.',
            ]);
        }

        $data = $response->json();

        return [
            'status' => $data['status'] ?? null,
            'results' => is_array($data['results'] ?? null) ? $data['results'] : [],
            'error_message' => is_string($data['error_message'] ?? null) ? $data['error_message'] : null,
        ];
    }

    /**
     * Build a list of fallback queries that commonly resolve “parish vs church” naming differences.
     *
     * @return array<int, string>
     */
    protected function buildFallbackQueries(string $query): array
    {
        $normalized = trim($query);
        $lower = mb_strtolower($normalized);

        $fallbacks = [];

        // If user didn't specify Catholic, try common Catholic disambiguators.
        if (! str_contains($lower, 'catholic')) {
            $fallbacks[] = $normalized.' Catholic Church';
            $fallbacks[] = $normalized.' Catholic Parish';
        }

        // Swap parish/church terms.
        if (str_contains($lower, 'parish')) {
            $fallbacks[] = trim(preg_replace('/\bparish\b/i', 'Church', $normalized) ?? $normalized);
        }

        if (str_contains($lower, 'church')) {
            $fallbacks[] = trim(preg_replace('/\bchurch\b/i', 'Parish', $normalized) ?? $normalized);
        }

        // Ensure unique, non-empty, and not identical to original.
        return collect($fallbacks)
            ->map(fn (string $q) => trim($q))
            ->filter(fn (string $q) => $q !== '' && $q !== $normalized)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Search for places by free-form text query.
     *
     * Uses Google Places Text Search endpoint.
     *
     * @return array{query: string, count: int, results: array<int, array{place_id: string, name: string|null, formatted_address: string|null}>}
     */
    public function searchText(
        string $query,
        int $limit = 5,
        ?float $lat = null,
        ?float $lng = null,
        ?int $radiusMeters = null,
    ): array {
        $apiKey = (string) config('services.google_places.api_key');

        if ($apiKey === '') {
            throw ValidationException::withMessages([
                'google_places' => 'Google Places API key is not configured. Set GOOGLE_PLACES_API_KEY.',
            ]);
        }

        $queriesToTry = [$query];

        $first = $this->performTextSearchRequest($query, $apiKey, $lat, $lng, $radiusMeters);
        $status = $first['status'];

        if ($status !== 'OK' && $status !== 'ZERO_RESULTS') {
            throw ValidationException::withMessages([
                'google_places' => $first['error_message'] ?? 'Google Places API returned an error.',
            ]);
        }

        $allRawResults = $first['results'];

        // Only do fallback expansion if the initial query yields no results.
        if (count($allRawResults) === 0) {
            foreach ($this->buildFallbackQueries($query) as $fallbackQuery) {
                $queriesToTry[] = $fallbackQuery;

                $attempt = $this->performTextSearchRequest($fallbackQuery, $apiKey, $lat, $lng, $radiusMeters);
                $attemptStatus = $attempt['status'];

                if ($attemptStatus !== 'OK' && $attemptStatus !== 'ZERO_RESULTS') {
                    throw ValidationException::withMessages([
                        'google_places' => $attempt['error_message'] ?? 'Google Places API returned an error.',
                    ]);
                }

                $allRawResults = array_merge($allRawResults, $attempt['results']);

                if (count($allRawResults) >= $limit) {
                    break;
                }
            }
        }

        $results = collect($allRawResults)
            ->map(function (array $item) {
                return [
                    'place_id' => (string) ($item['place_id'] ?? ''),
                    'name' => $item['name'] ?? null,
                    'formatted_address' => $item['formatted_address'] ?? null,
                ];
            })
            ->filter(fn (array $item) => $item['place_id'] !== '')
            ->unique('place_id')
            ->take($limit)
            ->values()
            ->all();

        return [
            'query' => $query,
            'count' => count($results),
            'results' => $results,
        ];
    }

    /**
     * Fetch canonical place details by place_id.
     *
     * @return array{place_id: string, name: string|null, formatted_address: string|null, maps_url: string|null, lat: float|null, lng: float|null}
     */
    public function getDetails(string $placeId): array
    {
        $apiKey = (string) config('services.google_places.api_key');

        if ($apiKey === '') {
            throw ValidationException::withMessages([
                'google_places' => 'Google Places API key is not configured. Set GOOGLE_PLACES_API_KEY.',
            ]);
        }

        try {
            $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'place_id,name,formatted_address,geometry/location,url',
                'key' => $apiKey,
            ])->throw();
        } catch (ConnectionException $e) {
            throw ValidationException::withMessages([
                'google_places' => 'Unable to reach Google Places API.',
            ]);

            // Common Catholic community / location synonyms (excluding pastoral center).
            // These are appended to help when the canonical label differs on Google Maps.
            $suffixes = [
                'Chapel',
                'Shrine',
                'Oratory',
                'Mission',
                'Basilica',
                'Cathedral',
            ];

            foreach ($suffixes as $suffix) {
                $fallbacks[] = $normalized.' '.$suffix;
                if (! str_contains($lower, 'catholic')) {
                    $fallbacks[] = $normalized.' Catholic '.$suffix;
                }
            }
        } catch (RequestException $e) {
            throw ValidationException::withMessages([
                'google_places' => 'Google Places API request failed.',
            ]);
        }

        $data = $response->json();

        $status = $data['status'] ?? null;

        if ($status !== 'OK') {
            $message = $data['error_message'] ?? 'Google Places API returned an error.';

            throw ValidationException::withMessages([
                'place_id' => $message,
            ]);
        }

        $result = $data['result'] ?? [];
        $location = $result['geometry']['location'] ?? [];

        $lat = isset($location['lat']) ? (float) $location['lat'] : null;
        $lng = isset($location['lng']) ? (float) $location['lng'] : null;

        return [
            'place_id' => (string) ($result['place_id'] ?? $placeId),
            'name' => $result['name'] ?? null,
            'formatted_address' => $result['formatted_address'] ?? null,
            'maps_url' => $result['url'] ?? null,
            'lat' => $lat,
            'lng' => $lng,
        ];
    }
}
