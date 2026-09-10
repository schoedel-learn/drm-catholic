<?php

namespace Tests\Feature\Demo;

use App\Http\Middleware\ProtectDemoAccount;
use App\Models\EntityType;
use App\Models\Jurisdiction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DemoAuthenticationSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('demo.enabled', true);
        config()->set('demo.user_email', 'demo@example.invalid');
    }

    public function test_demo_account_is_resolved_from_the_started_web_session(): void
    {
        $user = User::factory()->create([
            'email' => config('demo.user_email'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        auth()->forgetGuards();

        $this->put('/user/profile-information', [
            'name' => 'Changed Demo User',
            'email' => 'changed@example.invalid',
        ])->assertForbidden();

        $this->assertSame('demo@example.invalid', $user->fresh()->email);

        $webMiddleware = app(Kernel::class)->getMiddlewareGroups()['web'];

        $this->assertContains(ProtectDemoAccount::class, $webMiddleware);
        $this->assertLessThan(
            array_search(ProtectDemoAccount::class, $webMiddleware, true),
            array_search(StartSession::class, $webMiddleware, true),
        );
        $this->assertSame(
            ProtectDemoAccount::class,
            app('router')->getMiddleware()['protect-demo-account'] ?? null,
        );
    }

    public function test_demo_account_password_cannot_be_changed(): void
    {
        $user = $this->actingAsDemoUser();

        $this->put('/user/password', [
            'current_password' => 'password',
            'password' => 'changed-password',
            'password_confirmation' => 'changed-password',
        ])->assertForbidden();

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    #[DataProvider('twoFactorIdentityRoutes')]
    public function test_demo_account_cannot_access_two_factor_identity_routes(
        string $method,
        string $uri,
        string $routeName,
    ): void {
        $this->actingAsDemoUser();
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $this->assertRouteMatches($routeName, $method, $uri);
        $this->call($method, '/'.$uri)->assertForbidden();
    }

    public static function twoFactorIdentityRoutes(): array
    {
        return [
            'enable' => ['POST', 'user/two-factor-authentication', 'two-factor.enable'],
            'confirm' => ['POST', 'user/confirmed-two-factor-authentication', 'two-factor.confirm'],
            'disable' => ['DELETE', 'user/two-factor-authentication', 'two-factor.disable'],
            'QR code' => ['GET', 'user/two-factor-qr-code', 'two-factor.qr-code'],
            'secret key' => ['GET', 'user/two-factor-secret-key', 'two-factor.secret-key'],
            'recovery codes' => ['GET', 'user/two-factor-recovery-codes', 'two-factor.recovery-codes'],
            'regenerate recovery codes' => ['POST', 'user/two-factor-recovery-codes', 'two-factor.regenerate-recovery-codes'],
        ];
    }

    public function test_demo_account_cannot_enable_two_factor_authentication(): void
    {
        $user = $this->actingAsDemoUser();
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $this->post('/user/two-factor-authentication')->assertForbidden();

        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_recovery_codes);
    }

    public function test_demo_account_cannot_regenerate_recovery_codes(): void
    {
        $user = $this->actingAsDemoUser([
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt('secret'),
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['original-code'])),
        ]);
        $this->withSession(['auth.password_confirmed_at' => time()]);

        $this->post('/user/two-factor-recovery-codes')->assertForbidden();

        $this->assertSame(['original-code'], $user->fresh()->recoveryCodes());
    }

    public function test_demo_account_cannot_log_out_other_browser_sessions(): void
    {
        $this->actingAsDemoUser();

        $this->delete('/user/other-browser-sessions', [
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_demo_account_cannot_delete_its_profile_photo(): void
    {
        $this->actingAsDemoUser();
        $this->assertRouteMatches('current-user-photo.destroy', 'DELETE', 'user/profile-photo');

        $this->delete('/user/profile-photo')->assertForbidden();
    }

    public function test_demo_account_cannot_delete_itself(): void
    {
        $user = $this->actingAsDemoUser();

        $this->delete('/user', [
            'password' => 'password',
        ])->assertForbidden();

        $this->assertNotNull($user->fresh());
    }

    public function test_demo_account_can_update_crm_records(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'email' => config('demo.user_email'),
        ]);
        $jurisdiction = Jurisdiction::factory()->create();

        $user->personalTeam()->update(['jurisdiction_id' => $jurisdiction->id]);
        $user->switchTeam($user->personalTeam());

        EntityType::seedDefaults($jurisdiction);
        $entityType = EntityType::withoutGlobalScopes()
            ->where('jurisdiction_id', $jurisdiction->id)
            ->where('slug', EntityType::SLUG_PARISH)
            ->firstOrFail();
        $organization = Organization::factory()->create([
            'jurisdiction_id' => $jurisdiction->id,
            'entity_type_id' => $entityType->id,
            'name' => 'Before Demo Edit',
        ]);

        $this->actingAs($user)
            ->put(route('organizations.update', $organization), [
                'name' => 'After Demo Edit',
                'entity_type_id' => $entityType->id,
            ])
            ->assertRedirect();

        $this->assertSame('After Demo Edit', $organization->fresh()->name);
    }

    public function test_non_demo_user_remains_unrestricted_in_demo_mode(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/user/profile-information', [
                'name' => 'Normal User',
                'email' => 'normal@example.com',
            ])
            ->assertRedirect();

        $this->assertSame('normal@example.com', $user->fresh()->email);
    }

    public function test_demo_email_is_not_protected_when_demo_mode_is_disabled(): void
    {
        config()->set('demo.enabled', false);
        $user = User::factory()->create([
            'email' => config('demo.user_email'),
        ]);

        $this->actingAs($user)
            ->put('/user/profile-information', [
                'name' => 'Local User',
                'email' => 'local@example.com',
            ])
            ->assertRedirect();

        $this->assertSame('local@example.com', $user->fresh()->email);
    }

    private function actingAsDemoUser(array $attributes = []): User
    {
        $user = User::factory()->create([
            'email' => config('demo.user_email'),
            ...$attributes,
        ]);

        $this->actingAs($user);

        return $user;
    }

    private function assertRouteMatches(string $name, string $method, string $uri): void
    {
        $route = app('router')->getRoutes()->getByName($name);

        $this->assertNotNull($route);
        $this->assertContains($method, $route->methods());
        $this->assertSame($uri, $route->uri());
    }
}
