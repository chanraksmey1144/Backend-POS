<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
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
            'product_id'    => ['required', 'integer', 'exists:products,id'],
            'variant_id'    => ['nullable', 'integer', 'exists:product_variants,id'],
            'warehouse_id'  => ['nullable', 'integer', 'exists:warehouses,id'],
            'movement_date' => ['nullable', 'date'],
            'type'          => [
                'required',
                'string',
                'in:purchase,sale,return,damage,adjustment,transfer',
            ],
            'quantity'      => ['required', 'numeric', 'not_in:0'], // + for incoming, - for outgoing
            'before_stock'  => ['nullable', 'numeric'],
            'after_stock'   => ['nullable', 'numeric'],
            'reference'     => ['nullable', 'string', 'max:100'],
            'note'          => ['nullable', 'string'],
        ];
    }
}
