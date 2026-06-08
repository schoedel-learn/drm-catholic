<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $table = 'calendar_events';

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'all_day',
        'location',
        'category',
        'color',
        'created_by',
    ];

    protected $casts = [
        'start_at'  => 'datetime',
        'end_at'    => 'datetime',
        'all_day'   => 'boolean',
    ];

    /**
     * Default colors per category.
     */
    public const CATEGORY_COLORS = [
        'sacramental' => '#6f42c1', // purple
        'formation'   => '#0d6efd', // blue
        'parish'      => '#198754', // green
        'diocesan'    => '#4F46E5', // indigo (brand primary)
        'staff'       => '#fd7e14', // orange
    ];

    public const CATEGORIES = ['sacramental', 'formation', 'parish', 'diocesan', 'staff'];

    // ── Relationships ──

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Scopes ──

    public function scopeForDateRange($query, string $start, string $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            // Events that overlap the range
            $q->where('start_at', '<=', $end)
              ->where(function ($q2) use ($start) {
                  $q2->where('end_at', '>=', $start)
                     ->orWhereNull('end_at');
              });
        })->orderBy('start_at');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeUpcoming($query, int $limit = 5)
    {
        return $query->where('start_at', '>=', now())
                     ->orderBy('start_at')
                     ->limit($limit);
    }

    // ── Serialization ──

    /**
     * Convert to FullCalendar event format.
     */
    public function toFullCalendarEvent(): array
    {
        $color = $this->color ?? (self::CATEGORY_COLORS[$this->category] ?? '#405189');

        $event = [
            'id'              => $this->id,
            'title'           => $this->title,
            'start'           => $this->all_day
                ? $this->start_at->toDateString()
                : $this->start_at->toIso8601String(),
            'allDay'          => $this->all_day,
            'backgroundColor' => $color,
            'borderColor'     => $color,
            'extendedProps'   => [
                'type'        => 'event',
                'category'    => $this->category,
                'description' => $this->description,
                'location'    => $this->location,
                'created_by'  => $this->created_by,
            ],
        ];

        if ($this->end_at) {
            $event['end'] = $this->all_day
                ? $this->end_at->addDay()->toDateString() // FullCalendar exclusive end for all-day
                : $this->end_at->toIso8601String();
        }

        return $event;
    }
}
