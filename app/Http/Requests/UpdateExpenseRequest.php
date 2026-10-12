<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'  => 'sometimes|string',
            'amount'       => 'sometimes|numeric|min:0.01',
            'paid_by'      => 'sometimes|string',
            'split_type'   => 'sometimes|in:equal,exact,percentage',
            'participants' => 'sometimes|array',
        ];
    }
}