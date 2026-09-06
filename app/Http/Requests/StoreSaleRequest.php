<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
            'invoice_number' => ['nullable', 'string', 'max:50', 'unique:sales,invoice_number'],
            'customer_id'    => ['nullable', 'integer', 'exists:customers,id'],
            'cashier_id'     => ['nullable', 'integer', 'exists:users,id'],
            'branch_id'      => ['nullable', 'integer', 'exists:branches,id'],
            'register_id'    => ['nullable', 'integer', 'exists:registers,id'],
            'sale_date'      => ['nullable', 'date'],
            'subtotal'       => ['required', 'numeric', 'min:0'],
            'discount'       => ['nullable', 'numeric', 'min:0'],
            'tax'            => ['nullable', 'numeric', 'min:0'],
            'total'          => ['required', 'numeric', 'min:0'],
            'paid'           => ['required', 'numeric', 'min:0'],
            'change'         => ['nullable', 'numeric', 'min:0'],
            'payment_method' => [
                'nullable',
                'string',
                'in:cash,card,qr,bank_transfer,mobile_payment,credit,mixed',
            ],
        
            'status'         => [
                'nullable',
                'string',
                'in:completed,pending,cancelled,refunded,hold',
            ],
            'payment_status' => [
                'nullable',
                'string',
                'in:paid,partial,unpaid,refunded',
            ],
            'notes'          => ['nullable', 'string'],
            'created_by'     => ['nullable', 'integer', 'exists:users,id'],
            'items'          => ['nullable', 'array'],
            'items.*.product_id'  => ['nullable', 'integer', 'exists:products,id'],
            'items.*.variant_id'  => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.name'        => ['nullable', 'string', 'max:255'],
            'items.*.sku'         => ['nullable', 'string', 'max:50'],
            'items.*.price'       => ['required', 'numeric', 'min:0'],
            'items.*.cost'        => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity'    => ['required', 'numeric', 'gt:0'],
            'items.*.discount'    => ['nullable', 'numeric', 'min:0'],
            'items.*.tax'         => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
