<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use App\Models\Parish;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class GetParishTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'get-parish';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Fetch a single parish by its UUID.

        Use this when you already know the parish ID and need full details (name, diocese, address, contact info, mass schedule).
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
        ], [
            'id.required' => 'You must provide a parish id (UUID).',
            'id.uuid' => 'The parish id must be a valid UUID.',
        ]);

        /** @var Parish|null $parish */
        $parish = Parish::query()->find($validated['id']);

        if (! $parish) {
            return Response::error('Parish not found.');
        }

        $payload = [
            'parish' => [
                'id' => (string) $parish->id,
                'diocese_id' => (string) $parish->diocese_id,
                'name' => (string) $parish->name,
                'pastor' => $parish->pastor,
                'address' => $parish->address,
                'phone' => $parish->phone,
                'email' => $parish->email,
                'website' => $parish->website,
                'mass_schedule' => $parish->mass_schedule,
            ],
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        return Response::make(
            Response::text($json !== false ? $json : 'Unable to encode result payload.')
        )->withStructuredContent($payload);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\Contracts\JsonSchema\JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->string()
                ->description('Parish UUID.')
                ->required(),
        ];
    }

    /**
     * Get the tool's output schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'parish' => $schema->object()
                ->properties([
                    'id' => $schema->string()->required(),
                    'diocese_id' => $schema->string()->required(),
                    'name' => $schema->string()->required(),
                    'pastor' => $schema->string()->nullable(),
                    'address' => $schema->object()->nullable(),
                    'phone' => $schema->string()->nullable(),
                    'email' => $schema->string()->nullable(),
                    'website' => $schema->string()->nullable(),
                    'mass_schedule' => $schema->array()->nullable(),
                ])
                ->required(),
        ];
    }
}
