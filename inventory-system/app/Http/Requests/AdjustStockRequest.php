<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_stock' => 'required|integer|min:0|max:999999',
            'reference' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'actual_stock.required' => 'Actual stock count is required.',
            'actual_stock.min' => 'Stock cannot be negative.',
        ];
    }
}
