<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeldSaleRequest extends FormRequest
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
            'hold_number'  => ['nullable', 'string', 'max:50'],
            'customer_id'  => ['nullable', 'integer', 'exists:customers,id'],
            'cashier_id'   => ['nullable', 'integer', 'exists:users,id'],
            'discount'     => ['nullable', 'numeric', 'min:0'],
            'tax'          => ['nullable', 'numeric', 'min:0'],
            'total'        => ['required', 'numeric', 'min:0'],
            'items_json'   => ['required', 'array'],
            'items_json.*.product_id' => ['nullable', 'integer'],
            'items_json.*.name'       => ['required', 'string'],
            'items_json.*.price'      => ['required', 'numeric', 'min:0'],
            'items_json.*.quantity'   => ['required', 'numeric', 'min:0.001'],
        ];
    }
}
