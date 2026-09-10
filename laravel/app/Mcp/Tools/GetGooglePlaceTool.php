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
class GetGooglePlaceTool extends Tool
{
    protected string $name = 'get-google-place';

    protected string $description = <<<'MARKDOWN'
        Fetch Google Place Details for a specific `place_id`.

        Use this after selecting a candidate from `search-google-places`.
    MARKDOWN;

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'place_id' => ['required', 'string', 'min:1', 'max:255'],
        ], [
            'place_id.required' => 'You must provide a place_id.',
        ]);

        /** @var GooglePlacesClient $places */
        $places = app(GooglePlacesClient::class);

        $payload = $places->getDetails($validated['place_id']);

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
            'place_id' => $schema->string()
                ->description('Google place_id to lookup.')
                ->required(),
        ];
    }

    /**
     * @return array<string, Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'place_id' => $schema->string()->required(),
            'name' => $schema->string()->nullable(),
            'formatted_address' => $schema->string()->nullable(),
            'maps_url' => $schema->string()->nullable(),
            'lat' => $schema->number()->nullable(),
            'lng' => $schema->number()->nullable(),
        ];
    }
}
