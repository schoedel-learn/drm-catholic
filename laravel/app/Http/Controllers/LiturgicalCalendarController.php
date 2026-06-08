<?php

namespace App\Http\Controllers;

use App\Models\LiturgicalCalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiturgicalCalendarController extends Controller
{
    /**
     * Full calendar page view.
     */
    public function index()
    {
        $todayEvent = LiturgicalCalendarEvent::getToday();

        return view('velzon.calendar', [
            'todayEvent' => $todayEvent ? $todayEvent->toTodayCard() : null,
        ]);
    }

    /**
     * FullCalendar JSON event feed.
     *
     * GET /api/liturgical-calendar/events?start=&end=&overlay=true
     */
    public function events(Request $request): JsonResponse
    {
        // If overlay is explicitly disabled, return empty
        $overlay = filter_var($request->query('overlay', 'true'), FILTER_VALIDATE_BOOLEAN);
        if (!$overlay) {
            return response()->json([]);
        }

        $start = $request->query('start');
        $end = $request->query('end');

        if (!$start || !$end) {
            return response()->json(['error' => 'start and end parameters required'], 400);
        }

        $events = LiturgicalCalendarEvent::forDateRange($start, $end)->get();

        return response()->json(
            $events->map(fn($e) => $e->toFullCalendarEvent())->values()
        );
    }

    /**
     * Today's liturgical event for the Today's Liturgy card.
     *
     * GET /api/liturgical-calendar/today
     */
    public function today(): JsonResponse
    {
        $event = LiturgicalCalendarEvent::getToday();

        if (!$event) {
            return response()->json([
                'date' => today()->toDateString(),
                'title' => 'No liturgical data available',
                'rank' => 'weekday',
                'rank_icon' => '·',
                'color' => 'green',
                'color_hex' => '#198754',
                'season' => 'ordinary_time',
                'readings' => null,
                'notes' => null,
                'loth_volume' => null,
            ]);
        }

        return response()->json($event->toTodayCard());
    }
}
