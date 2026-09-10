<?php

namespace Tests\Feature\Demo;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_web_responses_are_not_indexable(): void
    {
        config()->set('demo.enabled', true);

        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_local_web_responses_do_not_force_a_robots_header(): void
    {
        config()->set('demo.enabled', false);

        $this->get('/login')
            ->assertOk()
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_demo_api_responses_do_not_receive_the_web_only_header(): void
    {
        config()->set('demo.enabled', true);

        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeaderMissing('X-Robots-Tag');
    }

    public function test_demo_authentication_redirect_is_not_indexable(): void
    {
        config()->set('demo.enabled', true);

        $this->get('/dashboard')
            ->assertRedirect('/login')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_demo_forbidden_response_is_not_indexable(): void
    {
        config()->set('demo.enabled', true);
        config()->set('demo.user_email', 'demo@example.invalid');
        $user = User::factory()->create([
            'email' => config('demo.user_email'),
        ]);

        $this->actingAs($user)
            ->put('/user/profile-information', [
                'name' => 'Changed Demo User',
                'email' => 'changed@example.invalid',
            ])
            ->assertForbidden()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_demo_not_found_response_is_not_indexable(): void
    {
        config()->set('demo.enabled', true);

        $this->get('/does-not-exist')
            ->assertNotFound()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_demo_api_errors_do_not_receive_the_web_header(): void
    {
        config()->set('demo.enabled', true);

        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertHeaderMissing('X-Robots-Tag');
    }
}
