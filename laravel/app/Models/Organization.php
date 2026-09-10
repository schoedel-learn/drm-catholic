<?php

namespace App\Models;

use App\Observers\TenantObserver;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'jurisdiction_id',
        'entity_type_id',
        'leader_id',
        'name',
        'address',
        'phone',
        'email',
        'website',
        'mass_schedule',
        'custom_data',
        'google_place_id',
        'google_formatted_address',
        'google_maps_url',
        'google_lat',
        'google_lng',
    ];

    protected $casts = [
        'address' => 'array',
        'mass_schedule' => 'array',
        'custom_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::observe(TenantObserver::class);
    }

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class);
    }

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }

    /**
     * Leader (typically a clergy contact)
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'leader_id');
    }

    /**
     * All clergy assignments to this organization
     */
    public function clergyAssignments(): HasMany
    {
        return $this->hasMany(ClergyAssignment::class);
    }

    /**
     * Current active clergy assignments
     */
    public function currentClergy(): HasMany
    {
        return $this->clergyAssignments()->active();
    }

    /**
     * Get the pastor (primary clergy assignment)
     */
    public function getPastorAttribute(): ?Clergy
    {
        $assignment = $this->currentClergy()
            ->where('is_primary', true)
            ->with('clergy.contact')
            ->first();

        return $assignment?->clergy;
    }

    /**
     * Scope for organizations of a specific entity type
     */
    public function scopeOfType($query, $entityTypeSlug)
    {
        return $query->whereHas('entityType', fn ($q) => $q->where('slug', $entityTypeSlug));
    }

    /**
     * Scope for parishes
     */
    public function scopeParishes($query)
    {
        return $query->ofType(EntityType::SLUG_PARISH);
    }

    /**
     * Scope for schools
     */
    public function scopeSchools($query)
    {
        return $query->ofType(EntityType::SLUG_SCHOOL);
    }
}
