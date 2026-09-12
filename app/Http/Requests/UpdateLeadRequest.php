<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'website' => 'nullable|string|url|max:255',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
            'priority' => 'nullable|string|in:low,medium,high',
            'notes' => 'nullable|string',
        ];
    }
}
