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
            'name' => 'required|string|max:255|unique:inventory_items,name',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50|in:pcs,yards,meters,rolls,packs,boxes',
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
