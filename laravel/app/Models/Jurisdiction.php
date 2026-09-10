<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Jurisdiction extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'type',
        'province',
        'state',
        'city',
        'established',
        'bishop',
        'website',
        'email',
        'phone',
        'address',
        'is_external',
        'locked',
    ];

    protected $casts = [
        'address' => 'array',
        'established' => 'date',
        'is_external' => 'boolean',
        'locked' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (! $model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}
