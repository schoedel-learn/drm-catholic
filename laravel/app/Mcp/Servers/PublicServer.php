<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetGooglePlaceTool;
use App\Mcp\Tools\PingTool;
use App\Mcp\Tools\SearchGooglePlacesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Tool;

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
        - search-google-places: Search Google Places for candidates.
        - get-google-place: Fetch Google Place details by place_id.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = [
        PingTool::class,
        SearchGooglePlacesTool::class,
        GetGooglePlaceTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<Server\Resource>>
     */
    protected array $resources = [
        //
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<Prompt>>
     */
    protected array $prompts = [
        //
    ];
}
