<?php

namespace App\Models;

use App\Observers\TenantObserver;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Contact extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'first_name',
        'last_name',
        'title',
        'role',
        'entity_type_id',
        'owner_jurisdiction_id',
        'jurisdiction_id',
        'organization_id',
        'email',
        'phone',
        'notes',
        'custom_data',
    ];

    protected $casts = [
        'custom_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::observe(TenantObserver::class);

        static::creating(function (self $model): void {
            if (! $model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class);
    }

    public function ownerJurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class, 'owner_jurisdiction_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }

    /**
     * One-to-one relationship with Clergy (if this contact is clergy)
     */
    public function clergy(): HasOne
    {
        return $this->hasOne(Clergy::class);
    }

    /**
     * Check if this contact is clergy
     */
    public function isClergy(): bool
    {
        return $this->clergy()->exists();
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get display name with title if clergy
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isClergy()) {
            return $this->clergy->formal_name;
        }

        return $this->full_name;
    }

    /**
     * Scope for contacts of a specific entity type
     */
    public function scopeOfType($query, $entityTypeSlug)
    {
        return $query->whereHas('entityType', fn ($q) => $q->where('slug', $entityTypeSlug));
    }

    /**
     * Scope for clergy contacts
     */
    public function scopeClergy($query)
    {
        return $query->has('clergy');
    }
}
