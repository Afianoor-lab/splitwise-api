<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'description'            => 'required|string|max:255',
            'amount'                 => 'required|numeric|gt:0',
            'paid_by'                => 'required|string',
            'split_type'             => 'required|in:equal,exact,percentage',
            'participants'           => 'required|array|min:1',
            'participants.*.user_id' => 'required|string',
            'participants.*.amount'  => 'required_if:split_type,exact|numeric|min:0',
            'participants.*.percentage' => 'required_if:split_type,percentage|numeric|min:0|max:100',
        ];
    }
}