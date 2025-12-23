<?php

namespace App\Mcp\Tools;

use App\Models\Parish;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
#[IsDestructive(false)]
class UpdateParishTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'update-parish';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Update an existing parish by UUID.

        Provide the parish id and any fields you want to change. Returns the updated parish.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id' => ['required', 'uuid'],
            'diocese_id' => ['sometimes', 'uuid'],
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'pastor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'array'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'mass_schedule' => ['sometimes', 'nullable', 'array'],
        ], [
            'id.required' => 'You must provide a parish id (UUID).',
            'id.uuid' => 'The parish id must be a valid UUID.',
            'website.url' => 'The website must be a valid URL (including https://).',
        ]);

        /** @var Parish|null $parish */
        $parish = Parish::query()->find($validated['id']);

        if (! $parish) {
            return Response::error('Parish not found.');
        }

        $update = collect($validated)
            ->except(['id'])
            ->all();

        if ($update === []) {
            return Response::error('No fields provided to update.');
        }

        $parish->fill($update);
        $parish->save();

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
            'diocese_id' => $schema->string()->description('Optional diocese (jurisdiction) UUID.'),
            'name' => $schema->string()->description('Optional updated parish name.'),
            'pastor' => $schema->string()->description('Optional updated pastor name.'),
            'address' => $schema->object()->description('Optional updated address object (stored as JSON).'),
            'phone' => $schema->string()->description('Optional updated phone number.'),
            'email' => $schema->string()->description('Optional updated email address.'),
            'website' => $schema->string()->description('Optional updated website URL (https://...).'),
            'mass_schedule' => $schema->array()->description('Optional updated mass schedule (stored as JSON array).'),
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
