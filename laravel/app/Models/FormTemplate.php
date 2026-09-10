<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'jurisdiction_id',
        'name',
        'description',
        'entity_type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function jurisdiction(): BelongsTo
    {
        return $this->belongsTo(Jurisdiction::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'template_id');
    }

    /**
     * Clone template fields to a new form
     */
    public function cloneFieldsTo(Form $form): void
    {
        // Find a form that was created from this template to clone fields
        $sourceForm = $this->forms()->has('fields')->first();

        if (! $sourceForm) {
            return;
        }

        foreach ($sourceForm->fields()->orderBy('order')->get() as $field) {
            $form->fields()->create([
                'type' => $field->type,
                'key' => $field->key,
                'label' => $field->label,
                'placeholder' => $field->placeholder,
                'help_text' => $field->help_text,
                'options' => $field->options,
                'validation' => $field->validation,
                'conditional' => $field->conditional,
                'crm_mapping' => $field->crm_mapping,
                'order' => $field->order,
            ]);
        }
    }
}
