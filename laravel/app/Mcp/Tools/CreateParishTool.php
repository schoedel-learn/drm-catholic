<?php

namespace App\Mcp\Tools;

use App\Models\Parish;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive(false)]
class CreateParishTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'create-parish';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Create a new parish.

        Provide the diocese_id, name, and address, plus optional contact details. Returns the created parish.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'diocese_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'pastor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['required', 'array'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'mass_schedule' => ['sometimes', 'nullable', 'array'],
        ], [
            'diocese_id.required' => 'You must provide a diocese_id (UUID).',
            'diocese_id.uuid' => 'The diocese_id must be a valid UUID.',
            'name.required' => 'You must provide a parish name.',
            'address.required' => 'You must provide an address object.',
            'website.url' => 'The website must be a valid URL (including https://).',
        ]);

        $parish = Parish::create([
            'diocese_id' => $validated['diocese_id'],
            'name' => $validated['name'],
            'pastor' => $validated['pastor'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
            'mass_schedule' => $validated['mass_schedule'] ?? null,
        ]);

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
            'diocese_id' => $schema->string()
                ->description('Diocese (jurisdiction) UUID.')
                ->required(),
            'name' => $schema->string()
                ->description('Parish name.')
                ->required(),
            'pastor' => $schema->string()
                ->description('Optional pastor name.'),
            'address' => $schema->object()
                ->description('Address object (stored as JSON).')
                ->required(),
            'phone' => $schema->string()
                ->description('Optional phone number.'),
            'email' => $schema->string()
                ->description('Optional email address.'),
            'website' => $schema->string()
                ->description('Optional website URL (https://...).'),
            'mass_schedule' => $schema->array()
                ->description('Optional mass schedule (stored as JSON array).'),
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
