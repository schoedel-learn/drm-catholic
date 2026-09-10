<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    /**
     * List events for FullCalendar (date range filter).
     * GET /api/calendar/events?start=&end=&category=
     */
    public function index(Request $request): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');

        if (! $start || ! $end) {
            return response()->json(['error' => 'start and end parameters required'], 400);
        }

        $query = CalendarEvent::forDateRange($start, $end);

        // Optional category filter
        if ($request->query('category')) {
            $query->byCategory($request->query('category'));
        }

        $events = $query->get()->map(fn ($e) => $e->toFullCalendarEvent());

        return response()->json($events->values());
    }

    /**
     * Create a new calendar event.
     * POST /api/calendar/events
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'category' => 'required|in:'.implode(',', CalendarEvent::CATEGORIES),
            'color' => 'nullable|string|max:7',
        ]);

        $validated['created_by'] = $request->user()->id;

        $event = CalendarEvent::create($validated);

        return response()->json($event->toFullCalendarEvent(), 201);
    }

    /**
     * Update an existing calendar event.
     * PUT /api/calendar/events/{event}
     */
    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_at' => 'sometimes|required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'boolean',
            'location' => 'nullable|string|max:255',
            'category' => 'sometimes|required|in:'.implode(',', CalendarEvent::CATEGORIES),
            'color' => 'nullable|string|max:7',
        ]);

        $event->update($validated);

        return response()->json($event->fresh()->toFullCalendarEvent());
    }

    /**
     * Delete a calendar event.
     * DELETE /api/calendar/events/{event}
     */
    public function destroy(CalendarEvent $event): JsonResponse
    {
        $event->delete();

        return response()->json(['message' => 'Event deleted'], 200);
    }
}
