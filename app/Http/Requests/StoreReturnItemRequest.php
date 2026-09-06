<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnItemRequest extends FormRequest
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
            'return_id'  => ['required', 'integer', 'exists:sale_returns,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'name'       => ['required', 'string', 'max:255'],
            'sku'        => ['nullable', 'string', 'max:50'],
            'price'      => ['required', 'numeric', 'min:0'],
            'cost'       => ['nullable', 'numeric', 'min:0'],
            'quantity'   => ['required', 'numeric', 'min:0.001'],
            'discount'   => ['nullable', 'numeric', 'min:0'],
            'tax'        => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
