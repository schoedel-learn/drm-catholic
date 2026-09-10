<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\EntityType;
use App\Models\Jurisdiction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoSeeder extends Seeder
{
    private const JURISDICTION_ID = '10000000-0000-4000-8000-000000000001';

    private const PARISH_TYPE_ID = '11000000-0000-4000-8000-000000000001';

    private const OFFICE_TYPE_ID = '11000000-0000-4000-8000-000000000002';

    private const PARISH_ID = '20000000-0000-4000-8000-000000000001';

    private const OUTREACH_ID = '20000000-0000-4000-8000-000000000002';

    public function run(): void
    {
        $password = config('demo.user_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('DEMO_USER_PASSWORD is required when demo mode is enabled.');
        }

        $jurisdiction = $this->seedJurisdiction();
        $this->seedEntityTypes();
        $this->seedUser($jurisdiction, $password);
        $this->seedOrganizationsAndContacts();
    }

    private function seedJurisdiction(): Jurisdiction
    {
        return Jurisdiction::query()->updateOrCreate(
            ['id' => self::JURISDICTION_ID],
            [
                'name' => 'Demo Diocese',
                'type' => 'diocese',
                'province' => 'Demo Province',
                'state' => 'EX',
                'city' => 'Demo City',
                'bishop' => 'Bishop Example',
                'website' => 'https://diocese.example.invalid',
                'email' => 'office@diocese.example.invalid',
                'phone' => '+1 202-555-0100',
                'address' => [
                    'street' => '100 Example Way',
                    'city' => 'Demo City',
                    'state' => 'EX',
                    'zip' => '00000',
                ],
                'is_external' => false,
                'locked' => false,
            ],
        );
    }

    private function seedEntityTypes(): void
    {
        EntityType::withoutGlobalScopes()->updateOrCreate(
            ['id' => self::PARISH_TYPE_ID],
            [
                'jurisdiction_id' => self::JURISDICTION_ID,
                'name' => 'Parish',
                'slug' => EntityType::SLUG_PARISH,
                'base_entity' => EntityType::BASE_ORGANIZATION,
                'icon' => 'building-2',
                'color' => 'emerald',
                'is_system' => true,
                'is_active' => true,
            ],
        );

        EntityType::withoutGlobalScopes()->updateOrCreate(
            ['id' => self::OFFICE_TYPE_ID],
            [
                'jurisdiction_id' => self::JURISDICTION_ID,
                'name' => 'Office',
                'slug' => EntityType::SLUG_OFFICE,
                'base_entity' => EntityType::BASE_ORGANIZATION,
                'icon' => 'briefcase',
                'color' => 'slate',
                'is_system' => true,
                'is_active' => true,
            ],
        );
    }

    private function seedUser(Jurisdiction $jurisdiction, string $password): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => config('demo.user_email')],
            [
                'name' => 'Demo User',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $team = $user->ownedTeams()->updateOrCreate(
            ['personal_team' => true],
            [
                'name' => 'Demo Workspace',
                'jurisdiction_id' => $jurisdiction->id,
            ],
        );

        $user->forceFill(['current_team_id' => $team->id])->save();
    }

    private function seedOrganizationsAndContacts(): void
    {
        Organization::withoutGlobalScopes()->updateOrCreate(
            ['id' => self::PARISH_ID],
            [
                'jurisdiction_id' => self::JURISDICTION_ID,
                'entity_type_id' => self::PARISH_TYPE_ID,
                'name' => 'Demo Parish',
                'address' => [
                    'street' => '200 Sample Street',
                    'city' => 'Demo City',
                    'state' => 'EX',
                    'zip' => '00001',
                ],
                'phone' => '+1 202-555-0101',
                'email' => 'office@parish.example.invalid',
                'website' => 'https://parish.example.invalid',
            ],
        );

        Organization::withoutGlobalScopes()->updateOrCreate(
            ['id' => self::OUTREACH_ID],
            [
                'jurisdiction_id' => self::JURISDICTION_ID,
                'entity_type_id' => self::OFFICE_TYPE_ID,
                'name' => 'Demo Outreach Center',
                'address' => [
                    'street' => '300 Placeholder Avenue',
                    'city' => 'Demo City',
                    'state' => 'EX',
                    'zip' => '00002',
                ],
                'phone' => '+1 202-555-0102',
                'email' => 'hello@outreach.example.invalid',
                'website' => 'https://outreach.example.invalid',
            ],
        );

        Contact::withoutGlobalScopes()->updateOrCreate(
            ['id' => '30000000-0000-4000-8000-000000000001'],
            [
                'first_name' => 'Alex',
                'last_name' => 'Example',
                'role' => 'Parish Coordinator',
                'owner_jurisdiction_id' => self::JURISDICTION_ID,
                'jurisdiction_id' => self::JURISDICTION_ID,
                'organization_id' => self::PARISH_ID,
                'email' => 'alex@parish.example.invalid',
                'phone' => '+1 202-555-0103',
                'notes' => 'Generated demo contact.',
            ],
        );

        Contact::withoutGlobalScopes()->updateOrCreate(
            ['id' => '30000000-0000-4000-8000-000000000002'],
            [
                'first_name' => 'Jordan',
                'last_name' => 'Sample',
                'role' => 'Outreach Coordinator',
                'owner_jurisdiction_id' => self::JURISDICTION_ID,
                'jurisdiction_id' => self::JURISDICTION_ID,
                'organization_id' => self::OUTREACH_ID,
                'email' => 'jordan@outreach.example.invalid',
                'phone' => '+1 202-555-0104',
                'notes' => 'Generated demo contact.',
            ],
        );
    }
}
