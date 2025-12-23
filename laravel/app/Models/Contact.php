<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'owner_diocese_id',
        'diocese_id',
        'parish_id',
        'email',
        'phone',
        'notes',
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

    public function ownerDiocese(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class, 'owner_diocese_id');
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class, 'parish_id');
    }
}
