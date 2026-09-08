<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
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
            'branch_id'      => ['nullable', 'integer', 'exists:branches,id'],
            'category'       => [
                'sometimes',
                'required',
                'string',
                'in:Rent,Utilities,Salaries,Supplies,Transportation,Maintenance,Marketing,Taxes,Other',
            ],
            'amount'         => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'payment_method' => [
                'sometimes',
                'string',
                'in:cash,card,qr,bank_transfer,mobile_payment,credit,mixed',
            ],
            'expense_date'   => ['sometimes', 'date'],
            'description'    => ['nullable', 'string'],
            'receipt'        => ['nullable', 'string', 'max:255'],
        ];
    }
}
