<?php

namespace App\Http\Controllers;

use App\Models\LiturgicalCalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VelzonController extends Controller
{
    /**
     * Show the staff dashboard.
     */
    public function dashboard()
    {
        // Today's liturgical event
        $todayEvent = LiturgicalCalendarEvent::getToday();
        $todayCard = $todayEvent ? $todayEvent->toTodayCard() : null;

        // LOTH volume ranges for the current year
        $lothRanges = $this->getLothRanges();

        // Upcoming calendar events for the widget
        $upcomingEvents = \App\Models\CalendarEvent::upcoming(5)->get();

        // Diocesan KPI metrics — safe counts with fallback to 0
        $metrics = [
            'contacts' => $this->safeCount(\App\Models\Contact::class),
            'parishes' => $this->safeOrganizationCountByType(\App\Models\EntityType::SLUG_PARISH),
            'clergy' => $this->safeCount(\App\Models\Clergy::class, ['status' => 'active']),
            'pendingForms' => $this->safeCount(\App\Models\FormSubmission::class, ['status' => 'pending']),
            'eventsThisWeek' => \App\Models\CalendarEvent::where('start_at', '>=', now())
                ->where('start_at', '<=', now()->endOfWeek())
                ->count(),
        ];

        return view('velzon.dashboard-crm', [
            'todayEvent' => $todayCard,
            'lothRanges' => $lothRanges,
            'metrics' => $metrics,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    /**
     * Show the analytics dashboard.
     */
    public function analytics()
    {
        return view('velzon.dashboard-analytics');
    }

    /**
     * Dynamic page rendering for Velzon views.
     */
    public function show(Request $request)
    {
        $path = $request->path();

        // Remove 'admin/' prefix if present
        $path = preg_replace('/^admin\//', '', $path);

        // Convert path to view name (e.g., contacts/index -> contacts.index)
        $viewName = str_replace('/', '.', $path);

        if (view()->exists('velzon.' . $viewName)) {
            return view('velzon.' . $viewName);
        }

        return abort(404);
    }

    /**
     * Get LOTH volume ranges for the current liturgical year.
     */
    private function getLothRanges(): array
    {
        $year = now()->year;

        // Try current year, fallback to previous year
        foreach ([$year, $year - 1] as $y) {
            $path = database_path("seeders/data/loth_volumes_{$y}.json");
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                return $data['ranges'] ?? [];
            }
        }

        return [];
    }

    /**
     * Safely count model records. Returns 0 if the table doesn't exist yet.
     */
    private function safeCount(string $modelClass, array $conditions = []): int
    {
        try {
            $query = $modelClass::query();
            foreach ($conditions as $column => $value) {
                $query->where($column, $value);
            }
            return $query->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function safeOrganizationCountByType(string $entityTypeSlug): int
    {
        try {
            return \App\Models\Organization::query()
                ->whereHas('entityType', fn($query) => $query->where('slug', $entityTypeSlug))
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
