<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => 'required_without:amount|exists:credit_packages,id',
            'amount' => 'required_without:package_id|numeric|min:100',
        ];
    }
}
