<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
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
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'contact_person'  => ['nullable', 'string', 'max:255'],
            'email'           => ['nullable', 'string', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'tax_number'      => ['nullable', 'string', 'max:50'],
            'address'         => ['nullable', 'string', 'max:255'],
            'total_purchases' => ['sometimes', 'numeric', 'min:0'],
            'outstanding'     => ['sometimes', 'numeric', 'min:0'],
            'status'          => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ];
    }
}
