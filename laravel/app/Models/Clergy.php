<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clergy extends Model
{
    use HasUuids;

    protected $table = 'clergy';

    protected $fillable = [
        'contact_id',
        'status',
        'ordination_date',
        'ordination_type',
        'ordination_diocese',
        'incardination_status',
        'incardination_diocese',
        'incardination_date',
        'faculties',
        'languages',
        'bio',
        'photo_url',
        'birth_date',
    ];

    protected $casts = [
        'ordination_date' => 'date',
        'incardination_date' => 'date',
        'birth_date' => 'date',
        'faculties' => 'array',
        'languages' => 'array',
    ];

    /**
     * Status options
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETIRED = 'retired';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_DECEASED = 'deceased';

    /**
     * Ordination types
     */
    public const ORDINATION_PRIEST = 'priest';
    public const ORDINATION_DEACON_PERMANENT = 'deacon_permanent';
    public const ORDINATION_DEACON_TRANSITIONAL = 'deacon_transitional';
    public const ORDINATION_BISHOP = 'bishop';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ClergyAssignment::class);
    }

    /**
     * Get current (active) assignments
     */
    public function currentAssignments(): HasMany
    {
        return $this->assignments()
            ->where('status', 'active')
            ->whereNull('end_date');
    }

    /**
     * Get primary assignment
     */
    public function primaryAssignment()
    {
        return $this->currentAssignments()->where('is_primary', true)->first();
    }

    /**
     * Get all assignment history ordered by date
     */
    public function assignmentHistory(): HasMany
    {
        return $this->assignments()->orderByDesc('start_date');
    }

    /**
     * Check if clergy is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get full name from contact
     */
    public function getFullNameAttribute(): string
    {
        return $this->contact?->first_name . ' ' . $this->contact?->last_name;
    }

    /**
     * Get display title based on ordination type
     */
    public function getTitleAttribute(): string
    {
        return match ($this->ordination_type) {
            self::ORDINATION_PRIEST => 'Fr.',
            self::ORDINATION_BISHOP => 'Bp.',
            self::ORDINATION_DEACON_PERMANENT, self::ORDINATION_DEACON_TRANSITIONAL => 'Dcn.',
            default => '',
        };
    }

    /**
     * Get formal name with title
     */
    public function getFormalNameAttribute(): string
    {
        $title = $this->title;
        return $title ? "{$title} {$this->full_name}" : $this->full_name;
    }

    /**
     * Check if clergy has specific faculty
     */
    public function hasFaculty(string $faculty): bool
    {
        return ($this->faculties[$faculty] ?? false) === true;
    }

    /**
     * Get years ordained
     */
    public function getYearsOrdainedAttribute(): ?int
    {
        if (!$this->ordination_date) {
            return null;
        }
        return $this->ordination_date->diffInYears(now());
    }
}
