<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product') instanceof \App\Models\Product
            ? $this->route('product')->id
            : $this->route('product');
        return [
            'category_id'     => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id'        => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id'         => ['nullable', 'integer', 'exists:units,id'],
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'sku'             => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'barcode'         => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'barcode')->ignore($productId),
            ],
                        'description'     => ['nullable', 'string'],
            'image_label'     => ['nullable', 'string', 'max:20'],
            'image_color'     => ['nullable', 'string', 'max:20'],
            'cost'            => ['sometimes', 'numeric', 'min:0'],
            'price'           => ['sometimes', 'required', 'numeric', 'min:0'],
            'wholesale_price' => ['sometimes', 'numeric', 'min:0'],
            'tax_percent'     => ['sometimes', 'numeric', 'between:0,100'],
            'track_inventory' => ['sometimes', 'boolean'],
            'min_stock'       => ['sometimes', 'numeric', 'min:0'],
            'max_stock'       => ['nullable', 'numeric'],
            'stock'           => ['sometimes', 'numeric', 'min:0'],
            'status'          => ['sometimes', 'required', 'string', 'in:active,archived,draft'],
        ];
    }
}
