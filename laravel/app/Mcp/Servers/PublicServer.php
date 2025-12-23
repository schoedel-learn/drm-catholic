<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\PingTool;
use App\Mcp\Tools\CreateParishTool;
use App\Mcp\Tools\CreateParishFromGooglePlaceTool;
use App\Mcp\Tools\GetGooglePlaceTool;
use App\Mcp\Tools\GetParishTool;
use App\Mcp\Tools\SearchGooglePlacesTool;
use App\Mcp\Tools\SearchParishesTool;
use App\Mcp\Tools\UpdateParishTool;
use Laravel\Mcp\Server;

class PublicServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Public Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        This is a minimal MCP server used for local testing.

        Available tools:
        - ping: Returns "pong".
        - search-parishes: Search parishes by name.
        - get-parish: Fetch a parish by id.
        - create-parish: Create a new parish.
        - update-parish: Update an existing parish.
        - search-google-places: Search Google Places for candidates.
        - get-google-place: Fetch Google Place details by place_id.
        - create-parish-from-google-place: Create a parish using a Google place_id.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        PingTool::class,
        SearchParishesTool::class,
        GetParishTool::class,
        CreateParishTool::class,
        UpdateParishTool::class,
        SearchGooglePlacesTool::class,
        GetGooglePlaceTool::class,
        CreateParishFromGooglePlaceTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
