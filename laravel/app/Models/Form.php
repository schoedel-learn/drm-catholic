<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasUuids;

    protected $fillable = [
        'jurisdiction_id',
        'template_id',
        'name',
        'description',
        'entity_type',
        'fields',
        'token',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'fields' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Form $form) {
            if (empty($form->token)) {
                $form->token = Str::random(64);
            }
        });
    }

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'template_id');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return url("/forms/{$this->token}");
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isAccessible(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Get all visible fields based on current form data
     */
    public function getVisibleFields(array $formData = []): \Illuminate\Support\Collection
    {
        return $this->formFields->filter(fn(FormField $field) => $field->shouldShow($formData));
    }

    /**
     * Generate Laravel validation rules for all fields
     */
    public function getValidationRules(array $formData = []): array
    {
        $rules = [];

        foreach ($this->getVisibleFields($formData) as $field) {
            $rules["fields.{$field->key}"] = $field->getValidationRules();
        }

        return $rules;
    }
}
