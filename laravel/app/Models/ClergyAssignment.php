<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClergyAssignment extends Model
{
    use HasUuids;

    protected $fillable = [
        'clergy_id',
        'organization_id',
        'role',
        'canonical_role',
        'start_date',
        'end_date',
        'effective_date',
        'is_primary',
        'is_residence',
        'status',
        'decree_number',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'effective_date' => 'date',
        'is_primary' => 'boolean',
        'is_residence' => 'boolean',
    ];

    /**
     * Assignment statuses
     */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REVOKED = 'revoked';

    /**
     * Common clerical roles
     */
    public const ROLE_PASTOR = 'Pastor';

    public const ROLE_PAROCHIAL_VICAR = 'Parochial Vicar';

    public const ROLE_ADMINISTRATOR = 'Administrator';

    public const ROLE_DEACON = 'Deacon';

    public const ROLE_CHAPLAIN = 'Chaplain';

    public const ROLE_RECTOR = 'Rector';

    public function clergy(): BelongsTo
    {
        return $this->belongsTo(Clergy::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if assignment is currently active
     */
    public function isCurrent(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->end_date === null;
    }

    /**
     * Check if assignment is in the past
     */
    public function isPast(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast();
    }

    /**
     * Get duration of assignment
     */
    public function getDurationAttribute(): string
    {
        $start = $this->start_date;
        $end = $this->end_date ?? now();

        $years = $start->diffInYears($end);
        $months = $start->diffInMonths($end) % 12;

        if ($years > 0) {
            return $months > 0 ? "{$years}y {$months}m" : "{$years}y";
        }

        return "{$months}m";
    }

    /**
     * Scope for active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)->whereNull('end_date');
    }

    /**
     * Scope for assignments at a specific organization
     */
    public function scopeAtOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * End this assignment
     */
    public function end(?string $reason = null): void
    {
        $this->update([
            'end_date' => now(),
            'status' => self::STATUS_COMPLETED,
            'notes' => $reason ? ($this->notes ? "{$this->notes}\n{$reason}" : $reason) : $this->notes,
        ]);
    }
}
