<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lead_id' => 'required|exists:leads,id',
            'customer_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.service' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'terms' => 'nullable|string',
            'validity_days' => 'required|integer|min:1',
        ];
    }
}
