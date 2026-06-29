<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectLaborRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'worker_name' => 'nullable|string|max:255',
            'task' => 'required|string|max:255',
            'hours' => 'required|numeric|min:0.01',
            'hourly_rate' => 'required|numeric|min:0',
            'logged_at' => 'required|date',
            'remarks' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'task.required' => 'Describe the work that was done.',
            'hours.min' => 'Hours must be greater than zero.',
        ];
    }
}
