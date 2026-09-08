<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
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
            'transfer_number'          => ['nullable', 'string', 'max:50', 'unique:stock_transfers,transfer_number'],
            'source_warehouse_id'      => ['required', 'integer', 'exists:warehouses,id'],
            'destination_warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
                'different:source_warehouse_id', // Destination cannot be the same as source
            ],
            'item_count'               => ['nullable', 'integer', 'min:0'],
            'status'                   => [
                'nullable',
                'string',
                'in:draft,requested,approved,in_transit,received,cancelled',
            ],
            'notes'                    => ['nullable', 'string'],
        ];
    }
}
