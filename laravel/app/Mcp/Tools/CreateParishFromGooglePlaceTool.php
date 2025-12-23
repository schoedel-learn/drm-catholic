<?php

namespace App\Mcp\Tools;

use App\Models\Parish;
use App\Services\GooglePlaces\GooglePlacesClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsDestructive(false)]
#[IsIdempotent]
class CreateParishFromGooglePlaceTool extends Tool
{
    protected string $name = 'create-parish-from-google-place';

    protected string $description = <<<'MARKDOWN'
        Create a parish from a Google `place_id`.

        This creates a new parish record and stores Google alignment fields (place_id, formatted address, maps url, lat/lng).
        The parish `address` JSON will be populated from the Google formatted address.
    MARKDOWN;

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'diocese_id' => ['required', 'uuid'],
            'place_id' => ['required', 'string', 'min:1', 'max:255'],
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
        ], [
            'diocese_id.required' => 'You must provide a diocese_id (jurisdiction UUID).',
            'diocese_id.uuid' => 'The diocese_id must be a valid UUID.',
            'place_id.required' => 'You must provide a Google place_id.',
        ]);

        $existing = Parish::query()
            ->where('google_place_id', $validated['place_id'])
            ->first();

        if ($existing instanceof Parish) {
            if ((string) $existing->diocese_id !== (string) $validated['diocese_id']) {
                return Response::error(
                    'This Google place_id is already linked to a parish in a different diocese. '
                    .'Requested diocese_id: '.$validated['diocese_id'].'. '
                    .'Existing parish_id: '.$existing->id.' (diocese_id: '.$existing->diocese_id.').'
                );
            }

            $payload = [
                'parish' => [
                    'id' => (string) $existing->id,
                    'diocese_id' => (string) $existing->diocese_id,
                    'name' => (string) $existing->name,
                    'address' => $existing->address,
                    'google_place_id' => $existing->google_place_id,
                    'google_formatted_address' => $existing->google_formatted_address,
                    'google_maps_url' => $existing->google_maps_url,
                    'google_lat' => $existing->google_lat,
                    'google_lng' => $existing->google_lng,
                ],
            ];

            $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

            return Response::make(
                Response::text($json !== false ? $json : 'Unable to encode result payload.')
            )->withStructuredContent($payload);
        }

        /** @var \App\Services\GooglePlaces\GooglePlacesClient $places */
        $places = app(GooglePlacesClient::class);
        $details = $places->getDetails($validated['place_id']);

        $name = $validated['name'] ?? $details['name'] ?? null;

        if (! is_string($name) || $name === '') {
            return Response::error('Unable to determine parish name from Google Place details. Provide `name`.');
        }

        $parish = Parish::create([
            'diocese_id' => $validated['diocese_id'],
            'name' => $name,
            'address' => [
                'formatted' => $details['formatted_address'],
                'source' => 'google_places',
            ],
            'google_place_id' => $details['place_id'],
            'google_formatted_address' => $details['formatted_address'],
            'google_maps_url' => $details['maps_url'],
            'google_lat' => $details['lat'],
            'google_lng' => $details['lng'],
        ]);

        $payload = [
            'parish' => [
                'id' => (string) $parish->id,
                'diocese_id' => (string) $parish->diocese_id,
                'name' => (string) $parish->name,
                'address' => $parish->address,
                'google_place_id' => $parish->google_place_id,
                'google_formatted_address' => $parish->google_formatted_address,
                'google_maps_url' => $parish->google_maps_url,
                'google_lat' => $parish->google_lat,
                'google_lng' => $parish->google_lng,
            ],
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return Response::make(
            Response::text($json !== false ? $json : 'Unable to encode result payload.')
        )->withStructuredContent($payload);
    }

    /**
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'diocese_id' => $schema->string()
                ->description('Diocese (jurisdiction) UUID.')
                ->required(),

            'place_id' => $schema->string()
                ->description('Google place_id to create parish from.')
                ->required(),

            'name' => $schema->string()
                ->description('Optional override for parish name. If omitted, uses Google Place name.'),
        ];
    }

    /**
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'parish' => $schema->object()->properties([
                'id' => $schema->string()->required(),
                'diocese_id' => $schema->string()->required(),
                'name' => $schema->string()->required(),
                'address' => $schema->object()->additionalProperties(true)->required(),
                'google_place_id' => $schema->string()->nullable(),
                'google_formatted_address' => $schema->string()->nullable(),
                'google_maps_url' => $schema->string()->nullable(),
                'google_lat' => $schema->number()->nullable(),
                'google_lng' => $schema->number()->nullable(),
            ])->required(),
        ];
    }
}
