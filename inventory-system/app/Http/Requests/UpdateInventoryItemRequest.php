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
            'name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:20',
            'unit' => 'required|string|max:50|in:pcs,sets,packs,boxes,yards,meters,rolls',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ];
    }
}
