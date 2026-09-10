<?php

namespace Tests\Feature;

use App\Models\LiturgicalCalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiturgicalCalendarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed some test liturgical events.
     */
    private function seedTestEvents(): void
    {
        LiturgicalCalendarEvent::create([
            'date' => '2026-01-01',
            'title' => 'SOLEMNITY OF MARY, THE HOLY MOTHER OF GOD',
            'rank' => 'solemnity',
            'color' => 'white',
            'color_hex' => '#f8f9fa',
            'season' => 'christmas',
            'year' => 2026,
            'readings' => ['citations' => ['Nm 6:22-27', 'Gal 4:4-7', 'Lk 2:16-21'], 'lectionary_numbers' => [18]],
            'notes' => ['Holy Day of Obligation'],
            'metadata' => ['loth_volume' => 'I', 'holy_day_of_obligation' => true, 'rank_priority' => 1],
        ]);

        LiturgicalCalendarEvent::create([
            'date' => '2026-01-02',
            'title' => 'Saints Basil the Great and Gregory Nazianzen',
            'rank' => 'memorial',
            'color' => 'white',
            'color_hex' => '#f8f9fa',
            'season' => 'christmas',
            'year' => 2026,
            'readings' => ['citations' => ['1 Jn 2:22-28', 'Jn 1:19-28'], 'lectionary_numbers' => [205]],
            'notes' => null,
            'metadata' => ['loth_volume' => 'I', 'holy_day_of_obligation' => false, 'rank_priority' => 3],
        ]);

        LiturgicalCalendarEvent::create([
            'date' => '2026-03-15',
            'title' => 'THIRD SUNDAY OF LENT',
            'rank' => 'solemnity',
            'color' => 'violet',
            'color_hex' => '#6f42c1',
            'season' => 'lent',
            'year' => 2026,
            'readings' => ['citations' => ['Ex 3:1-8a, 13-15', '1 Cor 10:1-6, 10-12', 'Lk 13:1-9'], 'lectionary_numbers' => [30]],
            'notes' => null,
            'metadata' => ['loth_volume' => 'II', 'holy_day_of_obligation' => false, 'rank_priority' => 1],
        ]);
    }

    public function test_events_endpoint_returns_fullcalendar_format(): void
    {
        $this->seedTestEvents();

        $response = $this->actingAs($this->createUser())
            ->getJson('/api/liturgical-calendar/events?start=2026-01-01&end=2026-01-31&overlay=true');

        $response->assertOk()
            ->assertJsonCount(2) // 2 events in January
            ->assertJsonStructure([
                '*' => ['title', 'start', 'color', 'textColor', 'extendedProps' => ['rank', 'season', 'color_name']],
            ]);
    }

    public function test_events_overlay_false_returns_empty(): void
    {
        $this->seedTestEvents();

        $response = $this->actingAs($this->createUser())
            ->getJson('/api/liturgical-calendar/events?start=2026-01-01&end=2026-12-31&overlay=false');

        $response->assertOk()
            ->assertJsonCount(0);
    }

    public function test_today_endpoint_returns_card_data(): void
    {
        LiturgicalCalendarEvent::create([
            'date' => today()->toDateString(),
            'title' => 'Test Feast Day',
            'rank' => 'feast',
            'color' => 'red',
            'color_hex' => '#dc3545',
            'season' => 'ordinary_time',
            'year' => now()->year,
            'readings' => ['citations' => ['Acts 1:1-5'], 'lectionary_numbers' => [100]],
            'notes' => null,
            'metadata' => ['loth_volume' => 'III', 'rank_priority' => 2],
        ]);

        $response = $this->actingAs($this->createUser())
            ->getJson('/api/liturgical-calendar/today');

        $response->assertOk()
            ->assertJsonFragment(['title' => 'Test Feast Day'])
            ->assertJsonStructure(['date', 'title', 'rank', 'rank_icon', 'color', 'color_hex', 'season', 'readings', 'loth_volume']);
    }

    public function test_date_range_filtering(): void
    {
        $this->seedTestEvents();

        // Only March events
        $response = $this->actingAs($this->createUser())
            ->getJson('/api/liturgical-calendar/events?start=2026-03-01&end=2026-03-31&overlay=true');

        $response->assertOk()
            ->assertJsonCount(1);
    }

    public function test_events_requires_auth(): void
    {
        $response = $this->getJson('/api/liturgical-calendar/events?start=2026-01-01&end=2026-12-31&overlay=true');

        $response->assertUnauthorized();
    }

    /**
     * Create a test user.
     */
    private function createUser()
    {
        return User::factory()->create();
    }
}
