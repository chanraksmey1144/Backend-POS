<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name'       => ['required', 'string', 'max:100'],
            'sku'        => ['nullable', 'string', 'max:50', 'unique:product_variants,sku'],
            'barcode'    => ['nullable', 'string', 'max:50', 'unique:product_variants,barcode'],
            'cost'       => ['nullable', 'numeric', 'min:0'],
            'price'      => ['required', 'numeric', 'min:0'],
            'stock'      => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
