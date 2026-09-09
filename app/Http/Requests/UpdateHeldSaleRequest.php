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
            'items_json.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items_json.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items_json.*.name'       => ['required_with:items_json', 'string', 'max:255'],
            'items_json.*.sku'        => ['nullable', 'string', 'max:50'],
            'items_json.*.barcode'    => ['nullable', 'string', 'max:100'],
            'items_json.*.price'      => ['required_with:items_json', 'numeric', 'min:0'],
            'items_json.*.cost'       => ['nullable', 'numeric', 'min:0'],
            'items_json.*.quantity'   => ['required_with:items_json', 'numeric', 'min:0.001'],
            'items_json.*.discount'   => ['nullable', 'numeric', 'min:0'],
            'items_json.*.tax'        => ['nullable', 'numeric', 'between:0,100'],
            'items_json.*.stock'      => ['nullable', 'numeric'],
            'items_json.*.image'      => ['nullable', 'string', 'max:255'],
        ];
    }
}
