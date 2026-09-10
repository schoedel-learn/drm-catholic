<?php

namespace App\Mcp\Tools;

use App\Services\GooglePlaces\GooglePlacesClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class SearchGooglePlacesTool extends Tool
{
    protected string $name = 'search-google-places';

    protected string $description = <<<'MARKDOWN'
        Search Google Places by text query and return a short list of candidates.

        Use this tool to avoid long parish lists and select a canonical Google `place_id`.
    MARKDOWN;

    public function handle(Request $request): Response|ResponseFactory
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

        /** @var GooglePlacesClient $places */
        $places = app(GooglePlacesClient::class);

        $payload = $places->searchText(
            $validated['query'],
            $validated['limit'] ?? 5,
            array_key_exists('lat', $validated) ? (float) $validated['lat'] : null,
            array_key_exists('lng', $validated) ? (float) $validated['lng'] : null,
            array_key_exists('radius_meters', $validated) ? (int) $validated['radius_meters'] : null,
        );

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return Response::make(
            Response::text($json !== false ? $json : 'Unable to encode result payload.')
        )->withStructuredContent($payload);
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Text query. Example: "St Mary Madison WI".')
                ->required(),

            'limit' => $schema->integer()
                ->description('Maximum results to return (1-20).')
                ->default(5),

            'lat' => $schema->number()
                ->description('Optional latitude to bias results around a point (use with lng).'),

            'lng' => $schema->number()
                ->description('Optional longitude to bias results around a point (use with lat).'),

            'radius_meters' => $schema->integer()
                ->description('Optional radius in meters (1-50000) for location bias (requires lat/lng).')
                ->default(50000),
        ];
    }

    /**
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'count' => $schema->integer()->required(),
            'results' => $schema->array()
                ->items(
                    $schema->object()->properties([
                        'place_id' => $schema->string()->required(),
                        'name' => $schema->string()->nullable(),
                        'formatted_address' => $schema->string()->nullable(),
                    ])
                )
                ->required(),
        ];
    }
}
