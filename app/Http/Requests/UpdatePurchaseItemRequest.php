<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseItemRequest extends FormRequest
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
            'product_id'        => ['nullable', 'integer', 'exists:products,id'],
            'variant_id'        => ['nullable', 'integer', 'exists:product_variants,id'],
            'name'              => ['sometimes', 'required', 'string', 'max:255'],
            'sku'               => ['nullable', 'string', 'max:50'],
            'cost'              => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity'          => ['sometimes', 'required', 'numeric', 'min:0.001'],
            'received_quantity' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
