<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
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
            'name'       => ['sometimes', 'required', 'string', 'max:100'],
            'short_name' => ['sometimes', 'required', 'string', 'max:20'],
            'status'     => ['sometimes', 'required', 'string', 'max:20', 'in:active,inactive'],
        ];
    }
}
