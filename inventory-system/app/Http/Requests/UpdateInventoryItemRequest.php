<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:inventory_items,name,' . $this->route('inventoryItem')?->id,
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50|in:pcs,yards,meters,rolls,packs,boxes',
            'minimum_stock' => 'required|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ];
    }
}
