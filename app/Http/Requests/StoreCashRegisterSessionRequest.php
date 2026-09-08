<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashRegisterSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'register_id'   => ['required', 'integer', 'exists:registers,id'],
            'branch_id'     => ['nullable', 'integer', 'exists:branches,id'],
            'opening_cash'  => ['required', 'numeric', 'min:0'],
            'expected_cash' => ['nullable', 'numeric', 'min:0'],
            'notes'         => ['nullable', 'string'],
        ];
    }
}
