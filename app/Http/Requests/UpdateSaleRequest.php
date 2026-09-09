<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleRequest extends FormRequest
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
        $saleId = $this->route('sale') instanceof \App\Models\Sale
            ? $this->route('sale')->id
            : $this->route('sale');
        return [
            'invoice_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('sales', 'invoice_number')->ignore($saleId),
            ],
            'customer_id'    => ['nullable', 'integer', 'exists:customers,id'],
            'cashier_id'     => ['nullable', 'integer', 'exists:users,id'],
            'branch_id'      => ['nullable', 'integer', 'exists:branches,id'],
            'register_id'    => ['nullable', 'integer', 'exists:registers,id'],
            'sale_date'      => ['sometimes', 'date'],
            'subtotal'       => ['sometimes', 'numeric', 'min:0'],
            'discount'       => ['sometimes', 'numeric', 'min:0'],
            'tax'            => ['sometimes', 'numeric', 'min:0'],
            'total'          => ['sometimes', 'numeric', 'min:0'],
            'paid'           => ['sometimes', 'numeric', 'min:0'],
            'change'         => ['sometimes', 'numeric', 'min:0'],
                        'payment_method' => [
                'sometimes',
                'string',
                'in:cash,card,qr,bank_transfer,mobile_payment,credit,mixed',
            ],
            'status'         => [
                'sometimes',
                'string',
                'in:completed,pending,cancelled,refunded,hold',
            ],
            'payment_status' => [
                'sometimes',
                'string',
                'in:paid,partial,unpaid,refunded',
            ],
            'notes'          => ['nullable', 'string'],
            'created_by'     => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
