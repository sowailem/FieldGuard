<?php

namespace Sowailem\FieldGuard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateFieldGuardRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::any(['manage-field-guard', 'update-field-guard']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'model_class' => ['sometimes', 'required', 'string', function ($attribute, $value, $fail) {
                if (!class_exists($value) || !is_subclass_of($value, \Illuminate\Database\Eloquent\Model::class)) {
                    $fail("The {$attribute} must be a valid Eloquent model class.");
                }
            }],
            'field_name' => ['sometimes', 'required', 'string', 'max:255'],
            'read_policy' => ['nullable', 'array'],
            'write_policy' => ['nullable', 'array'],
            'mask' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
