<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferItemRequest extends FormRequest
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
            'transfer_id' => ['required', 'integer', 'exists:stock_transfers,id'],
            'product_id'  => ['required', 'integer', 'exists:products,id'],
            'variant_id'  => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity'    => ['required', 'numeric', 'min:0.001'],
        ];
    }
}
