<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAdjustCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => 'required|exists:tenants,id',
            'credits' => 'required|integer|min:1',
            'type' => 'required|string|in:add,remove',
            'reason' => 'required|string|max:255',
        ];
    }
}
