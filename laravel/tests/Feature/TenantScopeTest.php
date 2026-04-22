<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Jurisdiction;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_parishes_are_scoped_to_tenant()
    {
        // Setup Dioceses (Jurisdictions)
        $dioceseA = Jurisdiction::factory()->create(['name' => 'Diocese A']);
        $dioceseB = Jurisdiction::factory()->create(['name' => 'Diocese B']);

        // Setup Teams linked to Dioceses
        $userA = User::factory()->create();
        $teamA = Team::factory()->create([
            'user_id' => $userA->id,
            'jurisdiction_id' => $dioceseA->id,
            'personal_team' => true,
        ]);
        $userA->switchTeam($teamA);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create([
            'user_id' => $userB->id,
            'jurisdiction_id' => $dioceseB->id,
            'personal_team' => true,
        ]);
        $userB->switchTeam($teamB);

        // 1. User A creates a parish
        $this->actingAs($userA);

        $parishA = Organization::create([
            'name' => 'Organization A',
            'address' => ['street' => '123 Main St', 'city' => 'Metropolis', 'state' => 'NY', 'zip' => '10001'],
            // jurisdiction_id should be auto-set
        ]);

        $this->assertEquals($dioceseA->id, $parishA->jurisdiction_id, 'Organization should be assigned to Diocese A');

        // 2. User B creates a parish
        $this->actingAs($userB);

        // Assert User B cannot see Organization A
        $this->assertNull(Organization::find($parishA->id), 'User B should not see Organization A');

        $parishB = Organization::create([
            'name' => 'Organization B',
            'address' => ['street' => '456 Side St', 'city' => 'Gotham', 'state' => 'NY', 'zip' => '10002'],
        ]);

        $this->assertEquals($dioceseB->id, $parishB->jurisdiction_id, 'Organization should be assigned to Diocese B');

        // 3. Switch back to User A
        $this->actingAs($userA);

        // Assert User A sees Organization A but not Organization B
        $this->assertNotNull(Organization::find($parishA->id));
        $this->assertNull(Organization::find($parishB->id), 'User A should not see Organization B');
    }

    public function test_contacts_are_scoped_to_tenant()
    {
        // Setup Dioceses
        $dioceseA = Jurisdiction::factory()->create();
        $dioceseB = Jurisdiction::factory()->create();

        // Setup Users/Teams
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id, 'jurisdiction_id' => $dioceseA->id]);
        $userA->switchTeam($teamA);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id, 'jurisdiction_id' => $dioceseB->id]);
        $userB->switchTeam($teamB);

        // 1. User A creates a contact
        $this->actingAs($userA);

        $contactA = Contact::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'staff',
            // owner_diocese_id should be auto-set
        ]);

        $this->assertEquals($dioceseA->id, $contactA->owner_diocese_id, 'Contact should be owned by Diocese A');

        // 2. User B should not see Contact A
        $this->actingAs($userB);
        $this->assertNull(Contact::find($contactA->id), 'User B should not see Contact A');

        // 3. User B creates Contact B
        $contactB = Contact::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => 'volunteer',
        ]);

        $this->assertEquals($dioceseB->id, $contactB->owner_diocese_id, 'Contact should be owned by Diocese B');
    }
}
