<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
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
        $variantId = $this->route('product_variant') instanceof \App\Models\ProductVariant
            ? $this->route('product_variant')->id
            : $this->route('product_variant');
        return [
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'name'       => ['sometimes', 'required', 'string', 'max:100'],
            'sku'        => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('product_variants', 'sku')->ignore($variantId),
            ],
            'barcode'    => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('product_variants', 'barcode')->ignore($variantId),
            ],
            'cost'       => ['sometimes', 'numeric', 'min:0'],
            'price'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock'      => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
