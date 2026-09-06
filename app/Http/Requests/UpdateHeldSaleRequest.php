<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeldSaleRequest extends FormRequest
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
            'hold_number'  => ['sometimes', 'string', 'max:50'],
            'customer_id'  => ['nullable', 'integer', 'exists:customers,id'],
            'cashier_id'   => ['nullable', 'integer', 'exists:users,id'],
            'discount'     => ['sometimes', 'numeric', 'min:0'],
            'tax'          => ['sometimes', 'numeric', 'min:0'],
            'total'        => ['sometimes', 'required', 'numeric', 'min:0'],
            'items_json'   => ['sometimes', 'required', 'array'],
        ];
    }
}
