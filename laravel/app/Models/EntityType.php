<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EntityType extends Model
{
    use HasUuids;

    protected $fillable = [
        'jurisdiction_id',
        'name',
        'slug',
        'base_entity',
        'icon',
        'color',
        'description',
        'default_fields',
        'required_fields',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'default_fields' => 'array',
        'required_fields' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Base entity types
     */
    public const BASE_CONTACT = 'contact';
    public const BASE_ORGANIZATION = 'organization';
    public const BASE_STANDALONE = 'standalone';

    /**
     * System entity type slugs
     */
    public const SLUG_CLERGY = 'clergy';
    public const SLUG_PARISH = 'parish';
    public const SLUG_SCHOOL = 'school';
    public const SLUG_OFFICE = 'office';

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Scopes\TenantScope);
        static::observe(\App\Observers\TenantObserver::class);

        static::creating(function (EntityType $entityType) {
            if (empty($entityType->slug)) {
                $entityType->slug = Str::slug($entityType->name, '_');
            }
        });
    }

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Check if this type extends Contact
     */
    public function isContactBased(): bool
    {
        return $this->base_entity === self::BASE_CONTACT;
    }

    /**
     * Check if this type extends Organization
     */
    public function isOrganizationBased(): bool
    {
        return $this->base_entity === self::BASE_ORGANIZATION;
    }

    /**
     * Get icon with default fallback
     */
    public function getIconAttribute($value): string
    {
        if ($value)
            return $value;

        return match ($this->base_entity) {
            self::BASE_CONTACT => 'user',
            self::BASE_ORGANIZATION => 'building',
            default => 'box',
        };
    }

    /**
     * Get color with default fallback
     */
    public function getColorAttribute($value): string
    {
        if ($value)
            return $value;

        return match ($this->base_entity) {
            self::BASE_CONTACT => 'blue',
            self::BASE_ORGANIZATION => 'green',
            default => 'slate',
        };
    }

    /**
     * Seed default entity types for a jurisdiction
     */
    public static function seedDefaults(Jurisdiction $jurisdiction): void
    {
        $defaults = [
            [
                'name' => 'Clergy',
                'slug' => self::SLUG_CLERGY,
                'base_entity' => self::BASE_CONTACT,
                'icon' => 'church',
                'color' => 'purple',
                'is_system' => true,
            ],
            [
                'name' => 'Parish',
                'slug' => self::SLUG_PARISH,
                'base_entity' => self::BASE_ORGANIZATION,
                'icon' => 'building-2',
                'color' => 'emerald',
                'is_system' => true,
            ],
            [
                'name' => 'School',
                'slug' => self::SLUG_SCHOOL,
                'base_entity' => self::BASE_ORGANIZATION,
                'icon' => 'graduation-cap',
                'color' => 'blue',
                'is_system' => true,
            ],
            [
                'name' => 'Office',
                'slug' => self::SLUG_OFFICE,
                'base_entity' => self::BASE_ORGANIZATION,
                'icon' => 'briefcase',
                'color' => 'slate',
                'is_system' => true,
            ],
        ];

        foreach ($defaults as $default) {
            self::withoutGlobalScope(\App\Scopes\TenantScope::class)->updateOrCreate(
                ['jurisdiction_id' => $jurisdiction->id, 'slug' => $default['slug']],
                array_merge($default, ['jurisdiction_id' => $jurisdiction->id])
            );
        }
    }
}
