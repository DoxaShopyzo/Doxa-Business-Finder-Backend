<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Assume admin check is in middleware
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:tenants',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ];
    }
}
