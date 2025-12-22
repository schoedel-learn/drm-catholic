<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Parish extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'diocese_id',
        'name',
        'pastor',
        'address',
        'phone',
        'email',
        'website',
        'mass_schedule',
    ];

    protected $casts = [
        'address' => 'array',
        'mass_schedule' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (!$model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class, 'diocese_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'parish_id');
    }
}
