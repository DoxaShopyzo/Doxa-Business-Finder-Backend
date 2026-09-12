<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|unique:tenants,email,' . $this->route('tenant'),
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive,suspended',
        ];
    }
}
