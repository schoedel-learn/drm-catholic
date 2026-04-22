<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasUuids;

    protected $fillable = [
        'form_id',
        'type',
        'key',
        'label',
        'placeholder',
        'help_text',
        'options',
        'validation',
        'conditional',
        'crm_mapping',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation' => 'array',
        'conditional' => 'array',
    ];

    /**
     * Available field types
     */
    public const TYPES = [
        'text' => 'Text Input',
        'textarea' => 'Text Area',
        'email' => 'Email',
        'phone' => 'Phone Number',
        'number' => 'Number',
        'select' => 'Dropdown Select',
        'radio' => 'Radio Buttons',
        'checkbox' => 'Checkbox',
        'checkbox_group' => 'Checkbox Group',
        'date' => 'Date Picker',
        'file' => 'File Upload',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Check if this field is required
     */
    public function isRequired(): bool
    {
        return $this->validation['required'] ?? false;
    }

    /**
     * Check if this field should be visible based on conditional logic
     */
    public function shouldShow(array $formData): bool
    {
        if (empty($this->conditional)) {
            return true;
        }

        $dependsOn = $this->conditional['field_key'] ?? null;
        $operator = $this->conditional['operator'] ?? 'equals';
        $expectedValue = $this->conditional['value'] ?? null;

        if (!$dependsOn) {
            return true;
        }

        $actualValue = $formData[$dependsOn] ?? null;

        return match ($operator) {
            'equals' => $actualValue === $expectedValue,
            'not_equals' => $actualValue !== $expectedValue,
            'contains' => str_contains((string) $actualValue, (string) $expectedValue),
            'is_empty' => empty($actualValue),
            'is_not_empty' => !empty($actualValue),
            'greater_than' => (float) $actualValue > (float) $expectedValue,
            'less_than' => (float) $actualValue < (float) $expectedValue,
            default => true,
        };
    }

    /**
     * Get CRM field mapping components
     */
    public function getCrmMappingParts(): ?array
    {
        if (!$this->crm_mapping) {
            return null;
        }

        $parts = explode('.', $this->crm_mapping, 2);
        return [
            'type' => $parts[0] ?? null, // 'contact', 'organization', 'custom'
            'field' => $parts[1] ?? null,
        ];
    }

    /**
     * Get validation rules for Laravel validator
     */
    public function getValidationRules(): array
    {
        $rules = [];
        $v = $this->validation ?? [];

        if ($v['required'] ?? false) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-specific rules
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'number':
                $rules[] = 'numeric';
                if (isset($v['min']))
                    $rules[] = "min:{$v['min']}";
                if (isset($v['max']))
                    $rules[] = "max:{$v['max']}";
                break;
            case 'text':
            case 'textarea':
                if (isset($v['minLength']))
                    $rules[] = "min:{$v['minLength']}";
                if (isset($v['maxLength']))
                    $rules[] = "max:{$v['maxLength']}";
                if (isset($v['pattern']))
                    $rules[] = "regex:{$v['pattern']}";
                break;
            case 'file':
                $rules[] = 'file';
                if (isset($v['maxSize']))
                    $rules[] = "max:{$v['maxSize']}";
                if (isset($v['mimes']))
                    $rules[] = "mimes:{$v['mimes']}";
                break;
            case 'select':
            case 'radio':
                $optionValues = collect($this->options ?? [])->pluck('value')->implode(',');
                if ($optionValues) {
                    $rules[] = "in:{$optionValues}";
                }
                break;
        }

        return $rules;
    }
}
