<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReturnRequest extends FormRequest
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
            'sale_id'       => ['sometimes', 'required', 'integer', 'exists:sales,id'],
            'branch_id'     => ['nullable', 'integer', 'exists:branches,id'],
            'cashier_id'    => ['nullable', 'integer', 'exists:users,id'],
            'reason'        => ['nullable', 'string', 'max:255'],
            'refund_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
