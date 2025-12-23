<?php

namespace Tests\Unit\Mcp;

use App\Mcp\Servers\PublicServer;
use App\Mcp\Tools\CreateParishTool;
use App\Mcp\Tools\GetParishTool;
use App\Mcp\Tools\UpdateParishTool;
use App\Models\Jurisdiction;
use App\Models\Parish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParishToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_parish_tool_creates_a_parish(): void
    {
        $diocese = Jurisdiction::create([
            'name' => 'Diocese of Create',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'Exampleville',
            'address' => ['line1' => '1 Main St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $response = PublicServer::tool(CreateParishTool::class, [
            'diocese_id' => $diocese->id,
            'name' => 'St Create Parish',
            'address' => ['line1' => '10 Church St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
            'email' => 'create@example.test',
            'website' => 'https://example.test/st-create',
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('create-parish')
            ->assertSee('St Create Parish')
            ->assertSee('"diocese_id"');

        $this->assertDatabaseCount('parishes', 1);
        $this->assertDatabaseHas('parishes', ['name' => 'St Create Parish']);
    }

    public function test_get_parish_tool_returns_details(): void
    {
        $diocese = Jurisdiction::create([
            'name' => 'Diocese of Get',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'Exampleville',
            'address' => ['line1' => '1 Main St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $parish = Parish::create([
            'diocese_id' => $diocese->id,
            'name' => 'St Get Parish',
            'address' => ['line1' => '10 Church St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
            'mass_schedule' => [['day' => 'Sunday', 'time' => '10:00'] ],
        ]);

        $response = PublicServer::tool(GetParishTool::class, [
            'id' => $parish->id,
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('get-parish')
            ->assertSee('St Get Parish')
            ->assertSee('"mass_schedule"');
    }

    public function test_update_parish_tool_updates_fields(): void
    {
        $diocese = Jurisdiction::create([
            'name' => 'Diocese of Update',
            'type' => 'diocese',
            'province' => '',
            'state' => 'WI',
            'city' => 'Exampleville',
            'address' => ['line1' => '1 Main St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $parish = Parish::create([
            'diocese_id' => $diocese->id,
            'name' => 'St Before Update',
            'address' => ['line1' => '10 Church St', 'city' => 'Exampleville', 'state' => 'WI', 'postal' => '00000'],
        ]);

        $response = PublicServer::tool(UpdateParishTool::class, [
            'id' => $parish->id,
            'name' => 'St After Update',
            'email' => 'after-update@example.test',
        ]);

        $response
            ->assertOk()
            ->assertHasNoErrors()
            ->assertName('update-parish')
            ->assertSee('St After Update')
            ->assertSee('after-update@example.test');

        $this->assertDatabaseHas('parishes', [
            'id' => $parish->id,
            'name' => 'St After Update',
            'email' => 'after-update@example.test',
        ]);
    }
}
