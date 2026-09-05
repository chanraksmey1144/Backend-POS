<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id'     => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id'        => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id'         => ['nullable', 'integer', 'exists:units,id'],
            'name'            => ['required', 'string', 'max:255'],
            'sku'             => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'barcode'         => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'description'     => ['nullable', 'string'],
            'image_label'     => ['nullable', 'string', 'max:20'],
            'image_color'     => ['nullable', 'string', 'max:20'],
            'cost'            => ['nullable', 'numeric', 'min:0'],
            'price'           => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_percent'     => ['nullable', 'numeric', 'between:0,100'],
            'track_inventory' => ['nullable', 'boolean'],
            'min_stock'       => ['nullable', 'numeric', 'min:0'],
            'max_stock'       => ['nullable', 'numeric', 'gte:min_stock'],
            'stock'           => ['nullable', 'numeric', 'min:0'],
            'status'          => ['nullable', 'string', 'in:active,archived,draft'],
        ];
    }
}
