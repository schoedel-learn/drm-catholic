<?php

namespace Tests\Feature;

use App\Models\CustomField;
use App\Models\EntityType;
use App\Models\Jurisdiction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $jurisdiction;

    protected $parishType;

    protected $schoolType;

    protected $officeType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->withPersonalTeam()->create();
        $this->jurisdiction = Jurisdiction::factory()->create();

        $this->user->personalTeam()->update(['jurisdiction_id' => $this->jurisdiction->id]);
        $this->user->switchTeam($this->user->personalTeam());

        EntityType::seedDefaults($this->jurisdiction);
        $this->parishType = EntityType::withoutGlobalScopes()
            ->where('jurisdiction_id', $this->jurisdiction->id)
            ->where('slug', EntityType::SLUG_PARISH)
            ->firstOrFail();
        $this->schoolType = EntityType::withoutGlobalScopes()
            ->where('jurisdiction_id', $this->jurisdiction->id)
            ->where('slug', EntityType::SLUG_SCHOOL)
            ->firstOrFail();
        $this->officeType = EntityType::withoutGlobalScopes()
            ->where('jurisdiction_id', $this->jurisdiction->id)
            ->where('slug', EntityType::SLUG_OFFICE)
            ->firstOrFail();
    }

    public function test_can_list_organizations()
    {
        Organization::factory()->count(3)->create([
            'jurisdiction_id' => $this->jurisdiction->id,
            'entity_type_id' => $this->parishType->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('organizations.index'))
            ->assertStatus(200)
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Organizations/Index')
                    ->has('organizations.data', 3)
            );
    }

    public function test_can_filter_organizations_by_entity_type()
    {
        Organization::factory()->create([
            'jurisdiction_id' => $this->jurisdiction->id,
            'entity_type_id' => $this->parishType->id,
            'name' => 'St. Mary',
        ]);
        Organization::factory()->create([
            'jurisdiction_id' => $this->jurisdiction->id,
            'entity_type_id' => $this->schoolType->id,
            'name' => 'St. Mary School',
        ]);

        $this->actingAs($this->user)
            ->get(route('organizations.index', ['entity_type_id' => $this->parishType->id]))
            ->assertStatus(200)
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Organizations/Index')
                    ->has('organizations.data', 1)
                    ->where('organizations.data.0.entity_type.slug', EntityType::SLUG_PARISH)
                    ->where('organizations.data.0.name', 'St. Mary')
            );
    }

    public function test_can_create_organization_with_custom_fields()
    {
        // specific custom field for organization
        CustomField::create([
            'jurisdiction_id' => $this->jurisdiction->id,
            'label' => 'Established Year',
            'key' => 'established_year',
            'type' => 'number',
            'entity_type' => 'organization',
            'order' => 1,
        ]);

        $this->actingAs($this->user)
            ->post(route('organizations.store'), [
                'name' => 'New Parish',
                'entity_type_id' => $this->parishType->id,
                'email' => 'new@parish.com',
                'custom_data' => [
                    'established_year' => 1950,
                ],
            ])
            ->assertRedirect(route('organizations.index'));

        $this->assertDatabaseHas('organizations', [
            'name' => 'New Parish',
            'email' => 'new@parish.com',
        ]);

        $org = Organization::where('name', 'New Parish')->first();
        $this->assertEquals(1950, $org->custom_data['established_year']);
    }

    public function test_can_update_organization()
    {
        $org = Organization::factory()->create([
            'jurisdiction_id' => $this->jurisdiction->id,
            'entity_type_id' => $this->parishType->id,
            'name' => 'Old Name',
        ]);

        $this->actingAs($this->user)
            ->put(route('organizations.update', $org), [
                'name' => 'Updated Name',
                'entity_type_id' => $this->officeType->id,
                'address' => ['city' => 'New City'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('organizations', [
            'id' => $org->id,
            'name' => 'Updated Name',
            'entity_type_id' => $this->officeType->id,
        ]);

        $org->refresh();
        $this->assertEquals('New City', $org->address['city']);
    }

    public function test_can_delete_organization()
    {
        $org = Organization::factory()->create([
            'jurisdiction_id' => $this->jurisdiction->id,
            'entity_type_id' => $this->parishType->id,
        ]);

        $this->actingAs($this->user)
            ->delete(route('organizations.destroy', $org))
            ->assertRedirect(route('organizations.index'));

        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }

    public function test_cannot_access_other_tenants_organizations()
    {
        $otherJurisdiction = Jurisdiction::factory()->create();
        $otherOrg = Organization::factory()->create(['jurisdiction_id' => $otherJurisdiction->id]);

        $this->actingAs($this->user)
            ->get(route('organizations.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('organizations.data', 0)
            );

        $this->actingAs($this->user)
            ->get(route('organizations.edit', $otherOrg))
            ->assertStatus(404); // TenantScope should hide it
    }
}
