<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
            'group_id'       => ['nullable', 'integer', 'exists:customer_groups,id'],
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'email'          => ['nullable', 'string', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'address'        => ['nullable', 'string', 'max:255'],
            'loyalty_points' => ['sometimes', 'integer', 'min:0'],
            'total_spent'    => ['sometimes', 'numeric', 'min:0'],
            'outstanding'    => ['sometimes', 'numeric', 'min:0'],
            'status'         => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ];
    }
}
