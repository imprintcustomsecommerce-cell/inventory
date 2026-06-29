<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf,ai,psd,svg|max:20480',
            'feedback' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Choose an artwork or proof file to upload.',
            'file.mimes' => 'Allowed types: JPG, PNG, GIF, WEBP, SVG, PDF, AI, PSD.',
            'file.max' => 'File may not be larger than 20 MB.',
        ];
    }
}
