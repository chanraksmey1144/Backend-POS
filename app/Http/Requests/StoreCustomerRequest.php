<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['nullable', 'string', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'address'        => ['nullable', 'string', 'max:255'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
            'total_spent'    => ['nullable', 'numeric', 'min:0'],
            'outstanding'    => ['nullable', 'numeric', 'min:0'],
            'status'         => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
