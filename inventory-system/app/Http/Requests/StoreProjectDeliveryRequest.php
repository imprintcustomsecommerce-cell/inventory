<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => 'nullable|string|max:255',
            'courier' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'scheduled_date' => 'nullable|date',
            'fee' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}
