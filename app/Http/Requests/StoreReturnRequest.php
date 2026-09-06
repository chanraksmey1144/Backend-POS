<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
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
            'sale_id'       => ['required', 'integer', 'exists:sales,id'],
            'branch_id'     => ['nullable', 'integer', 'exists:branches,id'],
            'cashier_id'    => ['nullable', 'integer', 'exists:users,id'],
            'reason'        => ['nullable', 'string', 'max:255'],
            'refund_amount' => ['required_without:items', 'numeric', 'min:0'],
            'items'         => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.name'       => ['nullable', 'string', 'max:255'],
            'items.*.sku'        => ['nullable', 'string', 'max:50'],
            'items.*.price'      => ['required', 'numeric', 'min:0'],
            'items.*.cost'       => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity'   => ['required', 'numeric', 'gt:0'],
            'items.*.discount'   => ['nullable', 'numeric', 'min:0'],
            'items.*.tax'        => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
