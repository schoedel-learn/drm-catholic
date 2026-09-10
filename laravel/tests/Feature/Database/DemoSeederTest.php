<?php

namespace Tests\Feature\Database;

use App\Models\Contact;
use App\Models\Jurisdiction;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_demo_database_seed_creates_the_documented_local_user(): void
    {
        config(['demo.enabled' => false]);

        $this->seed(DatabaseSeeder::class);

        $user = User::query()->sole();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotNull($user->personalTeam());
    }

    public function test_demo_database_seed_creates_deterministic_sample_data(): void
    {
        config([
            'demo.enabled' => true,
            'demo.user_password' => 'demo-test-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $user = User::query()->sole();

        $this->assertSame('Demo User', $user->name);
        $this->assertSame('demo@example.invalid', $user->email);
        $this->assertTrue(Hash::check('demo-test-password', $user->password));

        $this->assertSame([
            [
                'id' => '20000000-0000-4000-8000-000000000001',
                'name' => 'Demo Parish',
                'email' => 'office@parish.example.invalid',
                'website' => 'https://parish.example.invalid',
            ],
            [
                'id' => '20000000-0000-4000-8000-000000000002',
                'name' => 'Demo Outreach Center',
                'email' => 'hello@outreach.example.invalid',
                'website' => 'https://outreach.example.invalid',
            ],
        ], Organization::query()
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'website'])
            ->toArray());

        $this->assertSame([
            [
                'id' => '30000000-0000-4000-8000-000000000001',
                'first_name' => 'Alex',
                'last_name' => 'Example',
                'role' => 'Parish Coordinator',
                'organization_id' => '20000000-0000-4000-8000-000000000001',
                'email' => 'alex@parish.example.invalid',
            ],
            [
                'id' => '30000000-0000-4000-8000-000000000002',
                'first_name' => 'Jordan',
                'last_name' => 'Sample',
                'role' => 'Outreach Coordinator',
                'organization_id' => '20000000-0000-4000-8000-000000000002',
                'email' => 'jordan@outreach.example.invalid',
            ],
        ], Contact::query()
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'role', 'organization_id', 'email'])
            ->toArray());
    }

    public function test_demo_database_seed_contains_no_local_preview_account_or_non_invalid_domains(): void
    {
        config([
            'demo.enabled' => true,
            'demo.user_password' => 'demo-test-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);

        $identityValues = [
            ...User::query()->pluck('email'),
            ...Jurisdiction::query()->whereNotNull('email')->pluck('email'),
            ...Jurisdiction::query()->whereNotNull('website')->pluck('website'),
            ...Contact::query()->whereNotNull('email')->pluck('email'),
            ...Organization::query()->whereNotNull('email')->pluck('email'),
            ...Organization::query()->whereNotNull('website')->pluck('website'),
        ];

        foreach ($identityValues as $identityValue) {
            $domain = str_contains($identityValue, '@')
                ? substr(strrchr($identityValue, '@'), 1)
                : parse_url($identityValue, PHP_URL_HOST);

            $this->assertIsString($domain);
            $this->assertStringEndsWith('.invalid', $domain);
        }
    }

    public function test_demo_database_seed_fails_when_the_demo_password_is_missing(): void
    {
        config([
            'demo.enabled' => true,
            'demo.user_password' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DEMO_USER_PASSWORD is required when demo mode is enabled.');

        $this->seed(DatabaseSeeder::class);
    }
}
