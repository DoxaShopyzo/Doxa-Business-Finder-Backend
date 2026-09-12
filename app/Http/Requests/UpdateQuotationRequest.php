<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'nullable|string|max:255',
            'items' => 'nullable|array|min:1',
            'items.*.service' => 'required_with:items|string|max:255',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'terms' => 'nullable|string',
            'validity_days' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected',
        ];
    }
}
