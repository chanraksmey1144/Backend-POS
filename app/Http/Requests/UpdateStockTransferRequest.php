<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockTransferRequest extends FormRequest
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
        $transferId = $this->route('stock_transfer') instanceof \App\Models\StockTransfer
            ? $this->route('stock_transfer')->id
            : $this->route('stock_transfer');
        return [
            'transfer_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('stock_transfers', 'transfer_number')->ignore($transferId),
            ],
            'source_warehouse_id'      => ['sometimes', 'required', 'integer', 'exists:warehouses,id'],
            'destination_warehouse_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:warehouses,id',
                'different:source_warehouse_id',
            ],
            'item_count' => ['sometimes', 'integer', 'min:0'],
            'status'     => [
                'sometimes',
                'string',
                'in:draft,requested,approved,in_transit,received,cancelled',
            ],
                        'notes'      => ['nullable', 'string'],
            'items'      => ['sometimes', 'array'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'   => ['nullable', 'numeric', 'min:0.001'],
        ];
    }
}
