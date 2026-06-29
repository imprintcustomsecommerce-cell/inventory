<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'material_id' => 'nullable|exists:materials,id',
            'description' => 'required|string|max:255',
            'quantity_ordered' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
        ];
    }
}
