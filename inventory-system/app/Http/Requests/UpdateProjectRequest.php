<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_name' => 'required|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'product_type' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'quoted_price' => 'nullable|numeric|min:0',
            'status' => ['required', Rule::in(Project::STATUSES)],
            'due_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
        ];
    }
}
