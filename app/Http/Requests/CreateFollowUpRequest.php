<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lead_id' => 'required|exists:leads,id',
            'followup_date' => 'required|date|after_or_equal:today',
            'followup_time' => 'nullable|date_format:H:i',
            'type' => 'required|string|in:call,email,meeting',
            'notes' => 'nullable|string',
        ];
    }
}
