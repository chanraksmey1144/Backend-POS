<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'purchase_number' => ['nullable', 'string', 'max:50', 'unique:purchases,purchase_number'],
            'supplier_id'     => ['nullable', 'integer', 'exists:suppliers,id'],
            'branch_id'       => ['nullable', 'integer', 'exists:branches,id'],
            'warehouse_id'    => ['nullable', 'integer', 'exists:warehouses,id'],
            'order_date'      => ['nullable', 'date'],
            'expected_date'   => ['nullable', 'date', 'after_or_equal:order_date'],
            'received_at'     => ['nullable', 'date'],
            'subtotal'        => ['required', 'numeric', 'min:0'],
            'discount'        => ['nullable', 'numeric', 'min:0'],
            'tax'             => ['nullable', 'numeric', 'min:0'],
            'total'           => ['required', 'numeric', 'min:0'],
            'status'          => [
                'nullable',
                'string',
                'in:draft,ordered,partially_received,received,cancelled',
            ],
            'payment_status'  => [
                'nullable',
                'string',
                'in:paid,partial,unpaid,refunded',
            ],
            'notes'           => ['nullable', 'string'],
            'created_by'      => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
