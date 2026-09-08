<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseItemRequest extends FormRequest
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
            'purchase_id'       => ['required', 'integer', 'exists:purchases,id'],
            'product_id'        => ['nullable', 'integer', 'exists:products,id'],
            'variant_id'        => ['nullable', 'integer', 'exists:product_variants,id'],
            'name'              => ['required', 'string', 'max:255'],
            'sku'               => ['nullable', 'string', 'max:50'],
            'cost'              => ['required', 'numeric', 'min:0'],
            'quantity'          => ['required', 'numeric', 'min:0.001'],
            'received_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
