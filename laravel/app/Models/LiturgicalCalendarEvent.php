<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiturgicalCalendarEvent extends Model
{
    protected $table = 'liturgical_calendar_events';

    protected $fillable = [
        'date',
        'title',
        'rank',
        'color',
        'color_hex',
        'season',
        'year',
        'readings',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'readings' => 'array',
        'notes' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Liturgical color to hex mapping.
     */
    public const COLOR_MAP = [
        'violet' => '#6f42c1',
        'white' => '#f8f9fa',
        'red' => '#dc3545',
        'green' => '#198754',
        'rose' => '#e83e8c',
    ];

    /**
     * Rank priority mapping (1 = highest).
     */
    public const RANK_PRIORITY = [
        'solemnity' => 1,
        'feast' => 2,
        'memorial' => 3,
        'optional_memorial' => 4,
        'weekday' => 5,
    ];

    /**
     * Rank display icons.
     */
    public const RANK_ICONS = [
        'solemnity' => '✨',
        'feast' => '⭐',
        'memorial' => '●',
        'optional_memorial' => '○',
        'weekday' => '·',
    ];

    /**
     * Convert this event into a FullCalendar-ready array.
     */
    public function toFullCalendarEvent(): array
    {
        return [
            'title' => $this->title,
            'start' => $this->date->toDateString(),
            'color' => $this->color_hex,
            'textColor' => $this->color === 'white' ? '#333333' : '#ffffff',
            'extendedProps' => [
                'rank' => $this->rank,
                'rankIcon' => self::RANK_ICONS[$this->rank] ?? '',
                'season' => $this->season,
                'color_name' => $this->color,
                'readings' => $this->readings,
                'notes' => $this->notes,
                'loth_volume' => $this->metadata['loth_volume'] ?? null,
            ],
        ];
    }

    /**
     * Helper for the "Today's Liturgy" card.
     */
    public function toTodayCard(): array
    {
        return [
            'date' => $this->date->toDateString(),
            'title' => $this->title,
            'rank' => $this->rank,
            'rank_icon' => self::RANK_ICONS[$this->rank] ?? '',
            'color' => $this->color,
            'color_hex' => $this->color_hex,
            'season' => $this->season,
            'readings' => $this->readings,
            'notes' => $this->notes,
            'loth_volume' => $this->metadata['loth_volume'] ?? null,
            'liturgical_day_label' => $this->getLiturgicalDayLabel(),
        ];
    }

    /**
     * Compute a human-readable liturgical day label.
     * e.g. "Saturday of Week 5 in Ordinary Time", "2nd Sunday of Easter"
     */
    public function getLiturgicalDayLabel(): string
    {
        $seasonLabels = [
            'advent' => 'Advent',
            'christmas' => 'Christmas',
            'lent' => 'Lent',
            'triduum' => 'Triduum',
            'easter' => 'Easter',
            'ordinary_time' => 'Ordinary Time',
        ];

        $dayOfWeek = $this->date->format('l'); // Monday, Tuesday, etc.
        $seasonLabel = $seasonLabels[$this->season] ?? ucfirst(str_replace('_', ' ', $this->season));

        // If it's a Sunday, look for the week number in the title itself
        if ($this->date->isSunday()) {
            // Titles like "FOURTH SUNDAY IN ORDINARY TIME" or "2ND SUNDAY OF EASTER"
            return $this->formatSundayLabel($this->title, $seasonLabel);
        }

        // For weekdays, find the most recent Sunday to extract the week number
        $previousSunday = static::where('date', $this->date->copy()->previous('Sunday')->toDateString())
            ->orderByRaw("CASE rank
                WHEN 'solemnity' THEN 1
                WHEN 'feast' THEN 2
                WHEN 'memorial' THEN 3
                WHEN 'optional_memorial' THEN 4
                WHEN 'weekday' THEN 5
                ELSE 6 END")
            ->first();

        if ($previousSunday) {
            $weekNumber = $this->extractWeekNumber($previousSunday->title);
            if ($weekNumber) {
                return "{$dayOfWeek} of Week {$weekNumber} in {$seasonLabel}";
            }
        }

        // Fallback: day + season
        return "{$dayOfWeek} in {$seasonLabel}";
    }

    /**
     * Format a Sunday title into a clean label.
     */
    private function formatSundayLabel(string $title, string $seasonLabel): string
    {
        $weekNumber = $this->extractWeekNumber($title);
        if ($weekNumber) {
            $ordinal = $this->ordinal($weekNumber);
            return "{$ordinal} Sunday of {$seasonLabel}";
        }
        // If no number found, return title as-is (formatted)
        return ucwords(strtolower($title));
    }

    /**
     * Extract a week number from a title like "FIFTH SUNDAY IN ORDINARY TIME".
     */
    private function extractWeekNumber(string $title): ?int
    {
        $wordToNumber = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
            'fourth' => 4,
            'fifth' => 5,
            'sixth' => 6,
            'seventh' => 7,
            'eighth' => 8,
            'ninth' => 9,
            'tenth' => 10,
            'eleventh' => 11,
            'twelfth' => 12,
            'thirteenth' => 13,
            'fourteenth' => 14,
            'fifteenth' => 15,
            'sixteenth' => 16,
            'seventeenth' => 17,
            'eighteenth' => 18,
            'nineteenth' => 19,
            'twentieth' => 20,
            'twenty-first' => 21,
            'twenty-second' => 22,
            'twenty-third' => 23,
            'twenty-fourth' => 24,
            'twenty-fifth' => 25,
            'twenty-sixth' => 26,
            'twenty-seventh' => 27,
            'twenty-eighth' => 28,
            'twenty-ninth' => 29,
            'thirtieth' => 30,
            'thirty-first' => 31,
            'thirty-second' => 32,
            'thirty-third' => 33,
            'thirty-fourth' => 34,
        ];

        $lower = strtolower($title);

        // Match word ordinals
        foreach ($wordToNumber as $word => $num) {
            if (str_contains($lower, $word)) {
                return $num;
            }
        }

        // Match numeric ordinals (1st, 2nd, 3rd, 4th, etc.)
        if (preg_match('/(\d+)(?:st|nd|rd|th)/i', $title, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Convert number to ordinal string (1 → 1st, 2 → 2nd, etc.)
     */
    private function ordinal(int $n): string
    {
        $suffix = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if ($n % 100 >= 11 && $n % 100 <= 19) {
            return $n . 'th';
        }
        return $n . ($suffix[$n % 10] ?? 'th');
    }

    /**
     * Scope to filter events within a date range.
     */
    public function scopeForDateRange($query, string $start, string $end)
    {
        return $query->whereBetween('date', [$start, $end])->orderBy('date');
    }

    /**
     * Get today's liturgical event (highest rank if multiple).
     */
    public static function getToday(): ?self
    {
        return static::where('date', today())
            ->orderByRaw("CASE rank
                WHEN 'solemnity' THEN 1
                WHEN 'feast' THEN 2
                WHEN 'memorial' THEN 3
                WHEN 'optional_memorial' THEN 4
                WHEN 'weekday' THEN 5
                ELSE 6 END")
            ->first();
    }
}
