<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GooglePlaces\GooglePlacesClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GooglePlacesController extends Controller
{
    public function search(Request $request, GooglePlacesClient $places): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'lat' => ['sometimes', 'numeric', 'between:-90,90'],
            'lng' => ['sometimes', 'numeric', 'between:-180,180'],
            'radius_meters' => ['sometimes', 'integer', 'min:1', 'max:50000'],
        ], [
            'query.required' => 'You must provide a search query.',
            'query.max' => 'The search query must be 100 characters or less.',
            'limit.max' => 'The limit must be 20 or less.',
        ]);

        $payload = $places->searchText(
            $validated['query'],
            $validated['limit'] ?? 5,
            array_key_exists('lat', $validated) ? (float) $validated['lat'] : null,
            array_key_exists('lng', $validated) ? (float) $validated['lng'] : null,
            array_key_exists('radius_meters', $validated) ? (int) $validated['radius_meters'] : null,
        );

        return response()->json($payload);
    }

    public function details(Request $request, GooglePlacesClient $places): JsonResponse
    {
        $validated = $request->validate([
            'place_id' => ['required', 'string', 'min:1', 'max:255'],
        ], [
            'place_id.required' => 'You must provide a place_id.',
        ]);

        $payload = $places->getDetails($validated['place_id']);

        return response()->json($payload);
    }
}
