<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:20',
            'unit' => 'required|string|max:50|in:pcs,sets,packs,boxes,yards,meters,rolls',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required.',
            'name.unique' => 'This item name already exists.',
            'unit.required' => 'Unit is required.',
            'unit.in' => 'Invalid unit selected.',
            'current_stock.required' => 'Current stock is required.',
            'minimum_stock.required' => 'Minimum stock is required.',
        ];
    }
}
