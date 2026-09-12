<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'followup_date' => 'nullable|date',
            'followup_time' => 'nullable|date_format:H:i',
            'type' => 'nullable|string|in:call,email,meeting',
            'status' => 'nullable|string|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
            'outcome' => 'nullable|string',
        ];
    }
}
