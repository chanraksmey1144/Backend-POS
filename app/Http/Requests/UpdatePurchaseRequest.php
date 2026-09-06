<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequest extends FormRequest
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
        $purchaseId = $this->route('purchase') instanceof \App\Models\Purchase
            ? $this->route('purchase')->id
            : $this->route('purchase');
        return [
            'purchase_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('purchases', 'purchase_number')->ignore($purchaseId),
            ],
            'supplier_id'    => ['nullable', 'integer', 'exists:suppliers,id'],
            'branch_id'      => ['nullable', 'integer', 'exists:branches,id'],
            'warehouse_id'   => ['nullable', 'integer', 'exists:warehouses,id'],
            'order_date'     => ['sometimes', 'date'],
            'expected_date'  => ['nullable', 'date'],
            'received_at'    => ['nullable', 'date'],
            'subtotal'       => ['sometimes', 'numeric', 'min:0'],
            'discount'       => ['sometimes', 'numeric', 'min:0'],
            'tax'            => ['sometimes', 'numeric', 'min:0'],
            'total'          => ['sometimes', 'required', 'numeric', 'min:0'],
            'status'         => [
                'sometimes',
                'string',
                'in:draft,ordered,partially_received,received,cancelled',
            ],

                        'payment_status' => [
                'sometimes',
                'string',
                'in:paid,partial,unpaid,refunded',
            ],
            'notes'          => ['nullable', 'string'],
        ];
    }
}
