<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Jurisdiction;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizations_are_scoped_to_tenant()
    {
        $jurisdictionA = Jurisdiction::factory()->create(['name' => 'Diocese A']);
        $jurisdictionB = Jurisdiction::factory()->create(['name' => 'Diocese B']);

        $userA = User::factory()->create();
        $teamA = Team::factory()->create([
            'user_id' => $userA->id,
            'jurisdiction_id' => $jurisdictionA->id,
            'personal_team' => true,
        ]);
        $userA->switchTeam($teamA);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create([
            'user_id' => $userB->id,
            'jurisdiction_id' => $jurisdictionB->id,
            'personal_team' => true,
        ]);
        $userB->switchTeam($teamB);

        $this->actingAs($userA);

        $organizationA = Organization::create([
            'name' => 'Organization A',
            'address' => ['street' => '123 Main St', 'city' => 'Metropolis', 'state' => 'NY', 'zip' => '10001'],
        ]);

        $this->assertEquals($jurisdictionA->id, $organizationA->jurisdiction_id, 'Organization should be assigned to Jurisdiction A');

        $this->actingAs($userB);

        $this->assertNull(Organization::find($organizationA->id), 'User B should not see Organization A');

        $organizationB = Organization::create([
            'name' => 'Organization B',
            'address' => ['street' => '456 Side St', 'city' => 'Gotham', 'state' => 'NY', 'zip' => '10002'],
        ]);

        $this->assertEquals($jurisdictionB->id, $organizationB->jurisdiction_id, 'Organization should be assigned to Jurisdiction B');

        $this->actingAs($userA);

        $this->assertNotNull(Organization::find($organizationA->id));
        $this->assertNull(Organization::find($organizationB->id), 'User A should not see Organization B');
    }

    public function test_contacts_are_scoped_to_tenant()
    {
        $jurisdictionA = Jurisdiction::factory()->create();
        $jurisdictionB = Jurisdiction::factory()->create();

        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id, 'jurisdiction_id' => $jurisdictionA->id]);
        $userA->switchTeam($teamA);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id, 'jurisdiction_id' => $jurisdictionB->id]);
        $userB->switchTeam($teamB);

        $this->actingAs($userA);

        $contactA = Contact::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'staff',
        ]);

        $this->assertEquals($jurisdictionA->id, $contactA->owner_jurisdiction_id, 'Contact should be owned by Jurisdiction A');

        $this->actingAs($userB);
        $this->assertNull(Contact::find($contactA->id), 'User B should not see Contact A');

        $contactB = Contact::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => 'volunteer',
        ]);

        $this->assertEquals($jurisdictionB->id, $contactB->owner_jurisdiction_id, 'Contact should be owned by Jurisdiction B');
    }
}
