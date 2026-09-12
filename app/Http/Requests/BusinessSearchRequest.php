<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'location' => 'required|string|max:255',
            'radius' => 'nullable|integer|min:1|max:50000',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }
}
