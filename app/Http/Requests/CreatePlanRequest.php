<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'credits_included' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
