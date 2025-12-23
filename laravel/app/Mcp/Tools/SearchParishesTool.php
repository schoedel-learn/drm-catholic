<?php

namespace App\Mcp\Tools;

use App\Models\Parish;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsIdempotent]
class SearchParishesTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'search-parishes';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Search parishes by name and return a structured list of matching results.

        Use this tool when you need to find one or more parishes by partial name.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'diocese_id' => ['sometimes', 'uuid'],
        ], [
            'query.required' => 'You must provide a search query (e.g., "St Mary").',
            'query.max' => 'The search query must be 100 characters or less.',
            'limit.max' => 'The limit must be 50 or less.',
            'diocese_id.uuid' => 'The diocese_id must be a valid UUID.',
        ]);

        $query = $validated['query'];
        $limit = $validated['limit'] ?? 10;
        $dioceseId = $validated['diocese_id'] ?? null;

        $builder = Parish::query()
            ->select(['id', 'diocese_id', 'name', 'phone', 'email', 'website'])
            ->where('name', 'like', '%'.$query.'%');

        if (is_string($dioceseId)) {
            $builder->where('diocese_id', $dioceseId);
        }

        $results = $builder
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Parish $parish) => [
                'id' => (string) $parish->id,
                'diocese_id' => (string) $parish->diocese_id,
                'name' => (string) $parish->name,
                'phone' => $parish->phone,
                'email' => $parish->email,
                'website' => $parish->website,
            ])
            ->all();

        $payload = [
            'query' => $query,
            'count' => count($results),
            'results' => $results,
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
            'query' => $schema->string()
                ->description('Parish name query (partial match). Example: "St Mary".')
                ->required(),

            'limit' => $schema->integer()
                ->description('Maximum number of results to return (1-50).')
                ->default(10),

            'diocese_id' => $schema->string()
                ->description('Optional diocese (jurisdiction) UUID to filter parishes by.'),
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
            'query' => $schema->string()->required(),
            'count' => $schema->integer()->required(),
            'results' => $schema->array()
                ->items(
                    $schema->object()
                        ->properties([
                            'id' => $schema->string()->required(),
                            'diocese_id' => $schema->string()->required(),
                            'name' => $schema->string()->required(),
                            'phone' => $schema->string()->nullable(),
                            'email' => $schema->string()->nullable(),
                            'website' => $schema->string()->nullable(),
                        ])
                )
                ->required(),
        ];
    }
}
