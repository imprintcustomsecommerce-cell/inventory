<?php

namespace App\Http\Requests;

use App\Models\ProjectIssue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ProjectIssue::TYPES)],
            'reason' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'quantity_affected' => 'nullable|integer|min:0',
            'rework_cost' => 'nullable|numeric|min:0',
            'reported_at' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Choose the issue type.',
        ];
    }
}
