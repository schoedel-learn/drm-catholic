<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PreviewNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_render_the_dashboard_preview(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertStatus(200)
            ->assertInertia(
                fn (Assert $page) => $page->component('Dashboard')
            );
    }

    public function test_admin_contacts_redirects_to_the_contacts_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/contacts')
            ->assertRedirect('/contacts');
    }

    public function test_admin_organizations_redirects_to_the_organizations_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/organizations')
            ->assertRedirect('/organizations');
    }
}
